{{-- E-posta: Şikayete Yanıt Verildi --}}
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şikayetinize Yanıt Verildi</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #0d6efd; margin: 0;">Şikayetvar</h1>
    </div>

    <div style="background-color: #fff; padding: 30px; border: 1px solid #dee2e6; border-radius: 5px;">
        <h2 style="color: #198754;">Şikayetinize Yanıt Verildi!</h2>

        <p>Merhaba <strong>{{ $complaint->user->name }}</strong>,</p>

        <p><strong>{{ $complaint->brand->name }}</strong> markası, şikayetinize yanıt verdi.</p>

        <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #0d6efd; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #0d6efd;">Şikayetiniz</h3>
            <p><strong>{{ $complaint->title }}</strong></p>
            <p style="color: #6c757d; font-size: 14px;">
                {{ Str::limit($complaint->description, 150) }}
            </p>
        </div>

        <div style="background-color: #d1e7dd; padding: 15px; border-left: 4px solid #198754; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #0f5132;">Marka Yanıtı</h3>
            <p style="white-space: pre-wrap;">{{ $response->content }}</p>
            <p style="color: #6c757d; font-size: 12px; margin-top: 10px;">
                <strong>{{ $complaint->brand->name }}</strong> - {{ $response->created_at->format('d.m.Y H:i') }}
            </p>
        </div>

        <div style="margin: 30px 0; text-align: center;">
            <a href="{{ route('complaints.show', $complaint) }}"
               style="display: inline-block; background-color: #198754; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-right: 10px;">
                Yanıtı Görüntüle
            </a>
            <a href="{{ route('complaints.show', $complaint) }}#comments"
               style="display: inline-block; background-color: #0d6efd; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px;">
                Yorum Yap
            </a>
        </div>

        <div style="background-color: #cff4fc; padding: 15px; border-left: 4px solid #0dcaf0; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #055160;">Memnun Kaldınız mı?</h4>
            <p style="margin-bottom: 15px;">Yanıt sizin için tatmin edici miydi?</p>
            <div style="text-align: center;">
                <a href="{{ route('complaints.resolve', $complaint) }}"
                   style="display: inline-block; background-color: #198754; color: white; padding: 8px 20px; text-decoration: none; border-radius: 5px; margin: 5px;">
                    ✓ Evet, Sorunum Çözüldü
                </a>
                <a href="{{ route('complaints.show', $complaint) }}"
                   style="display: inline-block; background-color: #dc3545; color: white; padding: 8px 20px; text-decoration: none; border-radius: 5px; margin: 5px;">
                    ✗ Hayır, Çözülmedi
                </a>
            </div>
        </div>

        <p style="margin-top: 30px;">
            Saygılarımızla,<br>
            <strong>Şikayetvar Ekibi</strong>
        </p>
    </div>

    <div style="text-align: center; margin-top: 20px; padding: 20px; color: #6c757d; font-size: 12px;">
        <p>Bu e-posta otomatik olarak gönderilmiştir. Lütfen yanıtlamayın.</p>
        <p>
            <a href="{{ route('home') }}" style="color: #0d6efd; text-decoration: none;">Şikayetvar</a> |
            <a href="{{ route('profile.notifications') }}" style="color: #0d6efd; text-decoration: none;">Bildirimler</a> |
            <a href="{{ route('profile.edit') }}" style="color: #0d6efd; text-decoration: none;">Ayarlar</a>
        </p>
        <p>&copy; {{ date('Y') }} Şikayetvar. Tüm hakları saklıdır.</p>
    </div>
</body>
</html>
