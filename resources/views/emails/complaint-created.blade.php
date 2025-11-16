{{-- E-posta: Şikayet Oluşturuldu --}}
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şikayetiniz Alındı</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #0d6efd; margin: 0;">Şikayetvar</h1>
    </div>

    <div style="background-color: #fff; padding: 30px; border: 1px solid #dee2e6; border-radius: 5px;">
        <h2 style="color: #198754;">Şikayetiniz Alındı!</h2>

        <p>Merhaba <strong>{{ $complaint->user->name }}</strong>,</p>

        <p>{{ $complaint->brand->name }} markası hakkında yaptığınız şikayet başarıyla kaydedildi.</p>

        <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #0d6efd; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #0d6efd;">Şikayet Detayları</h3>
            <p><strong>Başlık:</strong> {{ $complaint->title }}</p>
            <p><strong>Marka:</strong> {{ $complaint->brand->name }}</p>
            <p><strong>Kategori:</strong> {{ $complaint->category->name }}</p>
            <p><strong>Tarih:</strong> {{ $complaint->created_at->format('d.m.Y H:i') }}</p>
        </div>

        <p><strong>Açıklama:</strong></p>
        <p style="background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
            {{ Str::limit($complaint->description, 200) }}
        </p>

        <div style="margin: 30px 0;">
            <a href="{{ route('complaints.show', $complaint) }}"
               style="display: inline-block; background-color: #0d6efd; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px;">
                Şikayeti Görüntüle
            </a>
        </div>

        <div style="background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #664d03;">Sonraki Adımlar</h4>
            <ul style="margin: 0; padding-left: 20px;">
                <li>Şikayetiniz moderatörlerimiz tarafından incelenecektir</li>
                <li>Onaylandıktan sonra herkese açık olarak yayınlanacaktır</li>
                <li>Marka yanıt verdiğinde e-posta ile bilgilendirileceksiniz</li>
                <li>Şikayetinizin durumunu profil sayfanızdan takip edebilirsiniz</li>
            </ul>
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
            <a href="{{ route('help') }}" style="color: #0d6efd; text-decoration: none;">Yardım</a> |
            <a href="{{ route('contact') }}" style="color: #0d6efd; text-decoration: none;">İletişim</a>
        </p>
        <p>&copy; {{ date('Y') }} Şikayetvar. Tüm hakları saklıdır.</p>
    </div>
</body>
</html>
