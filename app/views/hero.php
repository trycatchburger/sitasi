<!-- views/hero.php -->
<hero>
    
<!-- Hero Section -->
<section class="relative overflow-hidden py-20">
<div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between space-y-10 md:space-y-0">
    <!-- Teks di kiri -->
    <div class="md:w-1/2 space-y-6 animate-fade-in-left">
        <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-green-900 leading-tight drop-shadow-sm tracking-wide">
          Portal<br>
          Unggah Mandiri<br>
        </h1>
        <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-semibold text-amber-600 leading-tight drop-shadow-sm mb-4 tracking-wide">
          Perpustakaan STAIN Sultan Abdurrahman Kepulauan Riau
        </h2>
    <p class="text-sm sm:text-base md:text-lg lg:text-xl text-gray-700 leading-relaxed tracking-wide">
      Layanan perpustakaan yang dirancang untuk memudahkan mahasiswa dalam penyerahan skripsi secara mandiri melalui proses yang terstruktur.
    </p>
    <div class="flex space-x-4">
    <a href="#unggah" class="bg-amber-600 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:bg-green-700 hover:scale-105 transform transition">           
   AKSES UNGGAH MANDIRI
    </a>

    <a href="<?= url('submission/repository') ?>"
    class="border border-amber-600 text-amber-700 px-6 py-3 rounded-lg font-semibold hover:bg-amber-50 hover:scale-105 transform transition">
               
   LIHAT REPOSITORY
    </a>
</div>

  </div>

  <!-- Gambar di kanan -->
   
  <div class="md:w-1/2 relative animate-fade-in-right">
    <img src="<?= url('public/images/char.png') ?>" alt="Karakter Unggah" class="w-full max-w-md scale-150 mx-auto drop-shadow-2xl">
        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-2/3 h-4 bg-green-100 rounded-full blur-2xl opacity-70"></div>

  
  </div>
</div>




</section>


</hero>
