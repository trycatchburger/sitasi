<!-- views/header.php -->
<header>
    
<!-- Hero Section -->
<section class="w-full p-2 mt-2">
<div class="container mx-auto mt-0 px-1 flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-2">
  <!-- Teks di kiri -->
   <div class="pl-6 sm:pl-10 md:pl-16 lg:pl-20">

  <div class="md:w-10/12 pl-20">
    <h1 class="text-5xl font-serif font-semibold text-green-900 leading-tight mb-4">
      Portal<br>
      Unggah Mandiri<br>
    </h1>
    <h2 class="text-3xl font-serif font-semibold text-green-900 leading-tight mb-4">
      Perpustakaan STAIN Sultan Abdurrahman Kepulauan Riau
    </h2>
    <p class="text-gray-700 max-w-lg mb-6">
      Layanan perpustakaan yang dirancang untuk memudahkan mahasiswa dalam penyerahan skripsi secara mandiri melalui proses yang terstruktur.
    </p>
    
    <a href="#unggah"
   class="inline-block bg-amber-600 text-white px-6 py-3 rounded shadow-lg hover:bg-amber-700 font-bold transition duration-300 ease-in-out transform hover:scale-105">
      AKSES UNGGAH MANDIRI
    </a>

    <a href="<?= url('submission/repository') ?>"
   class="inline-block bg-amber-600 text-white px-6 py-3 rounded shadow-lg hover:bg-amber-700 font-bold transition duration-300 ease-in-out transform hover:scale-105">
      LIHAT REPOSITORY
    </a>

    <a href="<?= url('submission/journal_repository') ?>"
   class="inline-block bg-orange-600 text-white px-6 py-3 rounded shadow-lg hover:bg-orange-700 font-bold transition duration-300 ease-in-out transform hover:scale-105">
      LIHAT JURNAL REPOSITORY
    </a>

  </div>

  <!-- Gambar di kanan -->
   
  <div class="md:w-8/12 flex justify-end">
    <img src="<?= url('public/images/char.png') ?>" alt="Karakter" class="max-h-300 w-auto object-contain">
  </div>
</div>




</section>


</header>
