# 🎓 Mohaned Blog Project

**Web Tabanlı Blog Uygulaması (PHP & MySQL)**
**Öğrenci**: Mohaned
**Ders**: Web Programlama

Bu proje, modern bir arayüze (Dark Mode & Cyber-Glow) ve çoklu dil desteğine (Türkçe, İngilizce, Arapça) sahip dinamik bir blog sistemidir.

---

## 🚀 Kurulum ve Çalıştırma Rehberi (How to Run)

Projeyi bilgisayarınızda (Locahost) çalıştırmak için lütfen aşağıdaki adımları takip edin.

### 1. Gereksinimler
- **XAMPP** veya **WAMP** (PHP ve MySQL sunucusu).
- VS Code (veya herhangi bir kod editörü).

### 2. Veritabanı Kurulumu
1. **XAMPP Control Panel**'i açın ve **Apache** ile **MySQL** servislerini başlatın.
2. Tarayıcınızda [http://localhost/phpmyadmin](http://localhost/phpmyadmin) adresine gidin.
3. Sol menüden **Yeni** (New) diyerek yeni bir veritabanı oluşturun.
   - Veritabanı adı: `blog_db`
   - Karşılaştırma (Collation): `utf8mb4_general_ci`
4. Oluşturduğunuz `blog_db` veritabanını seçin.
5. Üst menüden **İçe Aktar (Import)** sekmesine tıklayın.
6. Proje klasöründeki `schema.sql` dosyasını seçin ve **İçe Aktar** (Go) butonuna basın.
   - *Bu işlem tabloları oluşturacak ve varsa örnek verileri yükleyecektir.*

### 3. Konfigürasyon (Önemli!)
Proje varsayılan olarak şu veritabanı ayarlarını kullanır (`config.php`):
- **Host**: `127.0.0.1`
- **Port**: `3307` (Eğer sizin MySQL portunuz 3306 ise `config.php` dosyasında 7. satırı `$port = 3306;` olarak değiştirin).
- **Kullanıcı**: `root`
- **Şifre**: (Boş)

### 4. Projeyi Çalıştırma
Aşağıdaki yöntemlerden birini kullanabilirsiniz:

**Yöntem A: Otomatik Başlatıcı (Önerilen - En Kolay)**
1. Proje ana klasöründeki **`START_WEBSITE.bat`** dosyasına çift tıklayın.
2. Sunucu otomatik başlar ve tarayıcı açılır.

**Yöntem B: Manuel Terminal Komutu**
1. Proje klasörünü VS Code ile açın.
2. Terminali açın (`Ctrl + "`) ve şu komutu yazın:
   ```bash
   php -S localhost:9999
   ```
3. Tarayıcınızda şu adrese gidin: **[http://localhost:9999](http://localhost:9999)**

**Yöntem B: XAMPP htdocs**
1. Tüm proje klasörünü `C:\xampp\htdocs\blog` içerisine kopyalayın.
2. Tarayıcıda `http://localhost/blog` adresine gidin.

---

## ✨ Özellikler (Features)

1.  **Cyber-Glow Arayüz**: 
    - Full Dark Mode tasarımı.
    - Neon mavi ve sarı renk paleti.
    - Glassmorphism (Cam efekti) kartlar ve menüler.
    - Arka planda hareketli ışık animasyonları.

2.  **Çoklu Dil Desteği (Multi-Language)**:
    - **TR / EN / AR** dilleri arasında anlık geçiş.
    - Arapça seçildiğinde arayüz otomatik olarak **RTL** (Sağdan Sola) düzenine geçer.

3.  **Kullanıcı Sistemi**:
    - Kayıt Ol / Giriş Yap.
    - Profil Düzenle (Fotoğraf Yükleme).
    - Güvenli oturum yönetimi.

4.  **Blog Yönetimi**:
    - Yazı Paylaşma (Resim yüklenebilir).
    - Yazıları Listeleme ve Okuma.
    - Yazılara Yorum Yapma.

---

## 📂 Proje Yapısı

- `index.php`: Ana sayfa ve yönlendirme merkezi.
- `config.php`: Veritabanı ayarları.
- `languages.php`: Dil çeviri dosyası.
- `style.css`: Tüm tasarı ve animasyon kodları.
- `admin/`: Yönetici paneli dosyaları.
- `uploads/`: Kullanıcıların yüklediği resimler.

---

*Başarılar!*
