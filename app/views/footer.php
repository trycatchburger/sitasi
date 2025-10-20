<footer class="bg-[#0a3b2c] text-white py-10 px-8">
  <div class="container mx-auto max-w-6xl">

    <!-- Grid 3 kolom -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
      
      <!-- Jam Kerja -->
      <div class="text-white space-y-2">
            <!-- Logo -->
      <div class="flex space-x-6 mt-4">
        <!-- Logo STAIN -->
          <div class="bg-white bg-opacity-90 p-1 rounded shadow-sm">
            <img src="<?= url('public/images/logo_stainkepri.png') ?>" alt="Logo STAIN" class="h-20 w-auto drop-shadow-md">
          </div>

        <!-- Logo Perpustakaan -->
          <div class="bg-white bg-opacity-90 p-1 rounded shadow-sm">
            <img src="<?= url('public/images/logo_perpustakaan.png') ?>" alt="Logo Perpustakaan" class="h-20 w-auto drop-shadow-md">
          </div>
      </div>

        <h3 class="font-semibold mb-2">Jam Kerja</h3>
        <p class="text-sm text-yellow-300 font-semibold">Senin - Jum'at<br>09.00 - 18.00 WIB</p>
        <p class="text-xs mt-1 text-gray-300 italic">Tutup pada hari Sabtu dan Minggu</p>
      </div>

      <!-- Hubungi Kami -->
      <div class="space-y-4">
        <h3 class="font-semibold mb-2">Hubungi Kami</h3>
        <div class="flex items-center space-x-3">
          <span>pustaka@stainkepri.ac.id</span>
        </div>

        <!-- Sosial Media + Logo -->
        <div class="md:w-1/3 flex flex-col items-center md:items-end space-y-4">
          <div class="flex space-x-4">
            <a href="https://chat.whatsapp.com/Ej8t2WbkCRv3XL2lD3MUiB" aria-label="Facebook" class="hover:scale-110 transition-transform">
              <img src="<?= url('public/images/icons/whatsapp.png') ?>" alt="Whatsapp Group" class="w-6 h-6 object-contain">
            </a>
            <a href="https://www.facebook.com/profile.php?viewas=100000686899395&id=61551720756265&locale=id_ID" aria-label="Facebook" class="hover:scale-110 transition-transform">
              <img src="<?= url('public/images/icons/facebook.png') ?>" alt="Facebook" class="w-6 h-6 object-contain">
            </a>
            <a href="https://www.instagram.com/stainsarlibrary/" aria-label="Twitter" class="hover:scale-110 transition-transform">
              <img src="<?= url('public/images/icons/instagram.png') ?>" alt="Instagram" class="w-6 h-6 object-contain">
            </a>
            <a href="https://www.youtube.com/@PerpustakanSTAINSarkepri" aria-label="Instagram" class="hover:scale-110 transition-transform">
              <img src="<?= url('public/images/icons/youtube.png') ?>" alt="Youtube" class="w-6 h-6 object-contain">
            </a>
          </div>
        </div>
      </div>

      <!-- Lokasi Kami -->
      <div class="space-y-4">
        <h3 class="font-semibold text-white mb-2">Lokasi Kami</h3>
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3287.9842069154124!2d104.51422647396397!3d0.998011262607834!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d96fa7c77b9e77%3A0x643ca44bd6caccc3!2sSTAIN%20Sultan%20Abdurrahman!5e1!3m2!1sid!2sid!4v1757395315092!5m2!1sid!2sid" 
          width="100%" 
          height="150" 
          style="border:0; border-radius: 8px;" 
          allowfullscreen="" 
          loading="lazy">
        </iframe>
      </div>

    </div>

    <!-- Garis dan Copyright -->
    <div class="border-t border-gray-600 pt-6 flex flex-col items-center space-y-4">
      <p class="text-xs text-gray-300 text-center">
        &copy; 2025 Perpustakaan STAIN Sultan Abdurrahman Kepulauan Riau. All rights reserved.
      </p>



  </div>
</footer>
