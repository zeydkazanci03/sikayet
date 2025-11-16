<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ModerationRule;
use Illuminate\Support\Facades\Log;

/**
 * Moderasyon işlemleri servis sınıfı
 * Service class for moderation operations
 */
class ModerationService
{
    protected array $bannedWords = [];
    protected array $spamPatterns = [];

    public function __construct()
    {
        $this->loadModerationRules();
    }

    /**
     * Moderasyon kurallarını yükle
     * Load moderation rules
     */
    protected function loadModerationRules(): void
    {
        $rules = ModerationRule::where('is_active', true)->get();

        foreach ($rules as $rule) {
            if ($rule->type === 'banned_word') {
                $this->bannedWords[] = $rule->pattern;
            } elseif ($rule->type === 'spam_pattern') {
                $this->spamPatterns[] = $rule->pattern;
            }
        }
    }

    /**
     * Şikayeti kontrol et
     * Check complaint
     */
    public function checkComplaint(Complaint $complaint): array
    {
        $issues = [];

        // Yasaklı kelime kontrolü
        // Check banned words
        $bannedWordCheck = $this->checkBannedWords($complaint->title . ' ' . $complaint->content);
        if (!empty($bannedWordCheck)) {
            $issues['banned_words'] = $bannedWordCheck;
            $complaint->update(['spam_score' => $complaint->spam_score + 20]);
        }

        // Spam kontrolü
        // Check spam
        $spamCheck = $this->checkSpamPatterns($complaint->title . ' ' . $complaint->content);
        if (!empty($spamCheck)) {
            $issues['spam_patterns'] = $spamCheck;
            $complaint->update(['spam_score' => $complaint->spam_score + 30]);
        }

        // URL kontrolü
        // Check URLs
        if ($this->containsExcessiveUrls($complaint->content)) {
            $issues['excessive_urls'] = true;
            $complaint->update(['spam_score' => $complaint->spam_score + 25]);
        }

        // Tekrarlanan karakter kontrolü
        // Check repeated characters
        if ($this->hasRepeatedCharacters($complaint->content)) {
            $issues['repeated_characters'] = true;
            $complaint->update(['spam_score' => $complaint->spam_score + 10]);
        }

        // Büyük harf kontrolü
        // Check uppercase
        if ($this->hasExcessiveUppercase($complaint->title . ' ' . $complaint->content)) {
            $issues['excessive_uppercase'] = true;
            $complaint->update(['spam_score' => $complaint->spam_score + 5]);
        }

        // Spam skoruna göre durumu güncelle
        // Update status based on spam score
        if ($complaint->spam_score >= 50) {
            $complaint->update(['status' => 'spam']);
            Log::warning('Complaint automatically marked as spam', [
                'complaint_id' => $complaint->id,
                'spam_score' => $complaint->spam_score,
            ]);
        } elseif ($complaint->spam_score >= 30) {
            $complaint->update(['priority' => 'high']);
            Log::info('Complaint marked as high priority for review', [
                'complaint_id' => $complaint->id,
                'spam_score' => $complaint->spam_score,
            ]);
        }

        // Sentiment analizi
        // Sentiment analysis
        $sentiment = $this->analyzeSentiment($complaint->content);
        $complaint->update([
            'sentiment' => $sentiment['sentiment'],
            'sentiment_score' => $sentiment['score'],
        ]);

        return $issues;
    }

    /**
     * Yasaklı kelimeleri kontrol et
     * Check banned words
     */
    protected function checkBannedWords(string $text): array
    {
        $found = [];
        $textLower = mb_strtolower($text);

        foreach ($this->bannedWords as $word) {
            if (mb_stripos($textLower, mb_strtolower($word)) !== false) {
                $found[] = $word;
            }
        }

        return $found;
    }

    /**
     * Spam desenlerini kontrol et
     * Check spam patterns
     */
    protected function checkSpamPatterns(string $text): array
    {
        $found = [];

        foreach ($this->spamPatterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $text)) {
                $found[] = $pattern;
            }
        }

        return $found;
    }

    /**
     * Aşırı URL içeriyor mu kontrol et
     * Check if contains excessive URLs
     */
    protected function containsExcessiveUrls(string $text): bool
    {
        $urlPattern = '/(https?:\/\/[^\s]+)/i';
        preg_match_all($urlPattern, $text, $matches);

        return count($matches[0]) > 3;
    }

    /**
     * Tekrarlanan karakterleri kontrol et
     * Check repeated characters
     */
    protected function hasRepeatedCharacters(string $text): bool
    {
        // 4 veya daha fazla aynı karakter arka arkaya
        // 4 or more same characters in a row
        return preg_match('/(.)\1{3,}/', $text) === 1;
    }

    /**
     * Aşırı büyük harf kullanımını kontrol et
     * Check excessive uppercase usage
     */
    protected function hasExcessiveUppercase(string $text): bool
    {
        $text = preg_replace('/[^a-zA-ZğüşıöçĞÜŞİÖÇ]/', '', $text);
        if (strlen($text) < 10) {
            return false;
        }

        $uppercaseCount = strlen(preg_replace('/[^A-ZĞÜŞİÖÇ]/', '', $text));
        $uppercaseRatio = $uppercaseCount / strlen($text);

        return $uppercaseRatio > 0.5; // %50'den fazla büyük harf
    }

    /**
     * Duygu analizi yap (basit versiyon)
     * Analyze sentiment (simple version)
     */
    protected function analyzeSentiment(string $text): array
    {
        // Pozitif ve negatif kelime listeleri
        // Positive and negative word lists
        $positiveWords = ['teşekkür', 'memnun', 'iyi', 'güzel', 'harika', 'çözüldü', 'yardımcı'];
        $negativeWords = ['kötü', 'berbat', 'rezalet', 'kızgın', 'öfkeli', 'mağdur', 'dolandırıldım', 'çözmüyor'];

        $textLower = mb_strtolower($text);
        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            $positiveCount += substr_count($textLower, $word);
        }

        foreach ($negativeWords as $word) {
            $negativeCount += substr_count($textLower, $word);
        }

        $totalSentiment = $positiveCount - $negativeCount;

        if ($totalSentiment > 2) {
            return ['sentiment' => 'positive', 'score' => min($totalSentiment * 10, 100)];
        } elseif ($totalSentiment < -2) {
            return ['sentiment' => 'negative', 'score' => max($totalSentiment * 10, -100)];
        } else {
            return ['sentiment' => 'neutral', 'score' => 0];
        }
    }

    /**
     * Kullanıcı güvenilirlik skorunu hesapla
     * Calculate user trust score
     */
    public function calculateUserTrustScore(\App\Models\User $user): float
    {
        $score = 50; // Başlangıç skoru / Starting score

        // Toplam şikayet sayısı
        // Total complaint count
        $complaintCount = $user->complaint_count;
        if ($complaintCount > 0) {
            $score += min($complaintCount * 2, 20); // Max +20
        }

        // Çözülen şikayet oranı
        // Resolved complaint rate
        if ($complaintCount > 0) {
            $resolvedRate = ($user->resolved_complaint_count / $complaintCount) * 100;
            $score += ($resolvedRate / 100) * 20; // Max +20
        }

        // Spam şikayetleri
        // Spam complaints
        $spamComplaints = $user->complaints()->where('status', 'spam')->count();
        $score -= $spamComplaints * 10; // Her spam -10

        // Reddedilen şikayetler
        // Rejected complaints
        $rejectedComplaints = $user->complaints()->where('status', 'rejected')->count();
        $score -= $rejectedComplaints * 5; // Her red -5

        // Hesap yaşı (ay)
        // Account age (months)
        $accountAgeMonths = $user->created_at->diffInMonths(now());
        $score += min($accountAgeMonths * 2, 10); // Max +10

        // Skoru 0-100 arasında tut
        // Keep score between 0-100
        return round(max(0, min(100, $score)), 2);
    }

    /**
     * Metni temizle
     * Clean text
     */
    public function cleanText(string $text): string
    {
        // HTML etiketlerini kaldır
        // Remove HTML tags
        $text = strip_tags($text);

        // Gereksiz boşlukları temizle
        // Clean excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Tekrarlanan karakterleri azalt
        // Reduce repeated characters
        $text = preg_replace('/(.)\1{3,}/', '$1$1$1', $text);

        return trim($text);
    }

    /**
     * İçerik uygun mu kontrol et
     * Check if content is appropriate
     */
    public function isContentAppropriate(string $text): bool
    {
        $bannedWords = $this->checkBannedWords($text);
        $spamPatterns = $this->checkSpamPatterns($text);

        return empty($bannedWords) && empty($spamPatterns);
    }
}
