# Bilet Satın Alma Platformu

Modern web teknolojileri kullanılarak geliştirilmiş dinamik, veritabanı destekli ve çok kullanıcılı otobüs bileti satış platformu.

## Özellikler

### Kullanıcı Rolleri
- **Ziyaretçi**: Sefer arama ve görüntüleme
- **Yolcu (User)**: Bilet satın alma, iptal etme, PDF indirme
- **Firma Admin**: Kendi firmasına ait sefer yönetimi
- **Admin**: Sistem geneli yönetim

### Ana Özellikler
- ✅ Sefer arama ve listeleme
- ✅ Kullanıcı kayıt/giriş sistemi
- ✅ Rol tabanlı yetkilendirme
- ✅ Bilet satın alma ve koltuk seçimi
- ✅ Kupon kodu sistemi
- ✅ Bilet iptal etme (1 saat kuralı)
- ✅ PDF bilet indirme
- ✅ Sanal kredi sistemi
- ✅ Firma ve sefer yönetimi
- ✅ Admin paneli

## Teknolojiler

- **Backend**: PHP 8.1
- **Veritabanı**: SQLite
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Containerization**: Docker

## Kurulum

### Docker ile Çalıştırma

1. Projeyi klonlayın:
```bash
git clone <repository-url>
cd bilet-satin-alma
```

2. Docker Compose ile çalıştırın:
```bash
docker-compose up -d
```

3. Tarayıcınızda `http://localhost:8080` adresine gidin.

### Manuel Kurulum

1. PHP 8.1+ ve Apache/Nginx kurulumu
2. SQLite desteği
3. Proje dosyalarını web sunucu dizinine kopyalayın
4. Veritabanı otomatik olarak oluşturulacaktır

## Test Hesapları

### Admin
- **Kullanıcı Adı**: admin
- **Şifre**: admin123

### Firma Admin
- **Kullanıcı Adı**: metro_admin
- **Şifre**: admin123

### Yolcu
- Kayıt ol sayfasından yeni hesap oluşturun

## Kullanım

### Yolcu İşlemleri
1. Ana sayfadan sefer arayın
2. Sefer detaylarını görüntüleyin
3. Koltuk seçin ve bilet satın alın
4. Hesabım sayfasından biletlerinizi yönetin

### Firma Admin İşlemleri
1. Firma panelinden giriş yapın
2. Yeni sefer ekleyin
3. Mevcut seferleri düzenleyin/silin

### Admin İşlemleri
1. Admin panelinden giriş yapın
2. Firmaları yönetin
3. Firma adminleri oluşturun
4. Kupon kodları oluşturun

## Veritabanı Şeması

- **users**: Kullanıcı bilgileri
- **companies**: Otobüs firmaları
- **routes**: Sefer bilgileri
- **tickets**: Bilet bilgileri
- **coupons**: İndirim kuponları

## Güvenlik

- SQL injection koruması (PDO prepared statements)
- XSS koruması (htmlspecialchars)
- CSRF koruması
- Session güvenliği
- Rol tabanlı erişim kontrolü

## Lisans

Bu proje eğitim amaçlı geliştirilmiştir.

## Geliştirici

Bilet Satın Alma Platformu - PHP Web Development Project
