<!-- views/alur.php -->
<alur>

<style>
  /* Responsif stepper */
  .stepper {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 40px;
    margin-top: 10px;
    flex-wrap: wrap;
    text-align: center;
  }

  @media (max-width: 768px) {
    .stepper {
      flex-direction: column;
      align-items: center;
      gap: 24px;
    }

    .step-line {
      display: none; /* garis dihapus biar rapi di HP */
    }

    .stepper p {
      font-size: 13px;
    }
  }

  @media (max-width: 480px) {
    .stepper p {
      font-size: 12px;
    }
  }

  .step-circle {
    width: 48px;
    height: 48px;
    background-color: #0B6E4F;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  }

  .step-line {
    width: 80px;
    height: 3px;
    background-color: #0B6E4F;
    align-self: center;
  }
</style>

<section id="alur" class="w-full bg-white rounded-xl shadow-md p-4 mt-4 text-center">
  <h3 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-green-900 leading-tight my-2">
    Alur Unggah Mandiri
  </h3>

<!-- Alur Penggunaan -->
<div class="stepper">

  <!-- Langkah 1 -->
  <div>
    <div style="display: flex; flex-direction: column; align-items: center;">
      <div class="step-circle">1</div>
      <p style="font-size: 14px; margin-top: 8px; line-height: 1.4;">
        Isi Data & Unggah<br> Sesuai Format
      </p>
    </div>
  </div>

  <div class="step-line"></div>

  <!-- Langkah 2 -->
  <div>
    <div style="display: flex; flex-direction: column; align-items: center;">
      <div class="step-circle">2</div>
      <p style="font-size: 14px; margin-top: 8px; line-height: 1.4;">
        Verifikasi oleh Petugas<br> Perpustakaan
      </p>
    </div>
  </div>

  <div class="step-line"></div>

  <!-- Langkah 3 -->
  <div>
    <div style="display: flex; flex-direction: column; align-items: center;">
      <div class="step-circle">3</div>
      <p style="font-size: 14px; margin-top: 8px; line-height: 1.4;">
        Bukti Diterima melalui<br> E-mail
      </p>
    </div>
  </div>

  <div class="step-line"></div>

  <!-- Langkah 4 -->
  <div>
    <div style="display: flex; flex-direction: column; align-items: center;">
      <div class="step-circle">4</div>
      <p style="font-size: 14px; margin-top: 8px; line-height: 1.4;">
        Bukti diserahkan ke<br> Perpustakaan
      </p>
    </div>
  </div>

</div>


</section>


</alur>
