<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Gizlilik Politikası | Sosyal Medya Planlama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" type="image/png" href="<?= base_url('/logo2.png'); ?>">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="mb-4">
      <a href="<?= base_url('/auth/login'); ?>" class="text-decoration-none">&larr; Giriş sayfasına dön</a>
    </div>

    <div class="card shadow-sm">
      <div class="card-body p-4 p-md-5">
        <h1 class="mb-2">Gizlilik Politikası</h1>
        <p class="text-muted mb-4">Son güncelleme: <?= date('Y-m-d'); ?></p>

        <p>
          Sosyal Medya Planlama (“Hizmet”), kullanıcıların kendi sosyal medya hesaplarını bağlayarak içerik planlaması ve yayınlaması için sunulur.
          Bu Gizlilik Politikası, hangi verileri topladığımızı, nasıl kullandığımızı ve nasıl koruduğumuzu açıklar.
        </p>

        <h2 class="h5 mt-4">1) Toplanan Veriler</h2>
        <ul>
          <li><strong>Hesap bilgileri:</strong> e-posta, kullanıcı kimliği (sisteme giriş için).</li>
          <li><strong>Sosyal hesap bağlantıları:</strong> bağlanan platform, hesap kimliği/username gibi temel bilgiler.</li>
          <li><strong>Yetkilendirme verileri (OAuth):</strong> erişim belirteçleri (access token), yenileme belirteçleri (refresh token) ve süre bilgileri.</li>
          <li><strong>İçerik verileri:</strong> kullanıcı tarafından yüklenen metin, görsel, video ve planlama zamanı.</li>
          <li><strong>Log & teknik veriler:</strong> hata kayıtları, işlem denemeleri, zaman damgaları (sistemin çalışması ve hata ayıklama için).</li>
        </ul>

        <h2 class="h5 mt-4">2) Verilerin Kullanım Amaçları</h2>
        <ul>
          <li>Planlanan paylaşımları oluşturmak ve yayınlamak</li>
          <li>Hesap bağlantılarını yönetmek ve erişim yetkilerini kullanmak</li>
          <li>Hata ayıklama, güvenlik ve sistem iyileştirmeleri</li>
          <li>Destek taleplerini yanıtlamak</li>
        </ul>

        <h2 class="h5 mt-4">3) Üçüncü Taraf Platformlar</h2>
        <p>
          Hizmet; TikTok, Meta (Instagram/Facebook) ve Google/YouTube gibi üçüncü taraf platformların API’lerini kullanır.
          Paylaşım işlemleri ilgili platformların kurallarına, limitlerine ve onay süreçlerine tabidir.
        </p>

        <h2 class="h5 mt-4">4) Paylaşım ve Aktarım</h2>
        <ul>
          <li>Verileriniz üçüncü kişilerle “satılmaz”.</li>
          <li>Paylaşım için gerekli olduğu ölçüde ilgili platformlara (ör. YouTube upload, TikTok publish) aktarım yapılır.</li>
          <li>Yasal zorunluluk halinde resmi mercilerle paylaşım yapılabilir.</li>
        </ul>

        <h2 class="h5 mt-4">5) Saklama Süreleri</h2>
        <ul>
          <li>Hesap ve planlama verileri, hesabınız aktif olduğu sürece veya hizmeti sağlamak için gerekli süre boyunca saklanır.</li>
          <li>Log kayıtları; güvenlik ve hata ayıklama amacıyla makul süre saklanabilir.</li>
          <li>Hesabınızı kapatmanız veya silme talebinde bulunmanız halinde, yasal yükümlülükler hariç veriler silinir/anonimleştirilebilir.</li>
        </ul>

        <h2 class="h5 mt-4">6) Güvenlik</h2>
        <p>
          Yetkilendirme belirteçleri gibi hassas verileri korumak için teknik ve idari önlemler uygularız.
          Ancak internet üzerinden iletimin %100 güvenli olduğu garanti edilemez.
        </p>

        <h2 class="h5 mt-4">7) Haklarınız</h2>
        <ul>
          <li>Verilerinize erişim talep etme</li>
          <li>Düzeltme talep etme</li>
          <li>Silme talep etme</li>
          <li>OAuth bağlantılarını iptal etme</li>
        </ul>

        <h2 class="h5 mt-4">8) İletişim</h2>
        <p class="mb-1">
          Gizlilikle ilgili talepleriniz için:
          <a href="mailto:destek@sosyalmedyaplanlama.com">destek@sosyalmedyaplanlama.com</a>
        </p>
        <p class="mb-0">
          Veri silme talebi için ayrıca şu sayfayı kullanabilirsiniz:
          <a href="<?= base_url('/data-deletion'); ?>">Veri Silme Politikası</a>
        </p>

        <hr class="my-4">
        <p class="text-muted small mb-0">
          Not: Bu metin bilgilendirme amaçlıdır ve hukuki danışmanlık değildir. Gerekirse hukuk danışmanınızla görüşünüz.
        </p>
      </div>
    </div>
  </div>
</body>
</html>
