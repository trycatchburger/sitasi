<!-- views/unggah.php -->
<unggah>

<section id="unggah" class="w-full bg-white rounded-xl shadow-md p-4 mt-4 text-center">
  <h3 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-semibold text-green-900 leading-tight my-2">
    Pilih Unggahan
  </h3>

  <div class="flex flex-row flex-wrap justify-center gap-6 sm:gap-8 mt-2">
    <!-- Kartu 1: Skripsi -->
    <a href="<?= url('submission/new') ?>" class="block">
      <div class="bg-green-500 text-white rounded-xl shadow-md w-64 sm:w-60 h-auto p-6 flex flex-col items-center hover:bg-green-600 transition">
        <div class="text-4xl mb-3">📄</div>
        <h3 class="text-base sm:text-lg font-bold">Unggah Skripsi</h3>
        <p class="text-xs sm:text-sm mt-2 text-green-100 text-center">Untuk mahasiswa program Sarjana (S1) yang telah lulus sidang akhir</p>
      </div>
    </a>

    <!-- Kartu 2: Tesis -->
    <a href="<?= url('submission/new') ?>" class="block">
      <div class="bg-sky-500 text-white rounded-xl shadow-md w-64 sm:w-60 h-auto p-6 flex flex-col items-center hover:bg-sky-600 transition">
        <div class="text-4xl mb-3">📄</div>
        <h3 class="text-base sm:text-lg font-bold">Unggah Tesis</h3>
        <p class="text-xs sm:text-sm mt-2 text-sky-100 text-center">Untuk mahasiswa program Magister (S2) yang telah lulus sidang akhir</p>
      </div>
    </a>

    <!-- Kartu 3: Jurnal -->
    <a href="<?= url('submission/new') ?>" class="block">
      <div class="bg-orange-500 text-white rounded-xl shadow-md w-64 sm:w-60 h-auto p-6 flex flex-col items-center hover:bg-orange-600 transition">
        <div class="text-4xl mb-3">📄</div>
        <h3 class="text-base sm:text-lg font-bold">Unggah Jurnal</h3>
        <p class="text-xs sm:text-sm mt-2 text-orange-100 text-center">Untuk dosen dan mahasiswa yang ingin mengunggah artikel ilmiah</p>
      </div>
    </a>
  </div>
</section>


