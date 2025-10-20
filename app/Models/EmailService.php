<?php

namespace App\Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use App\Exceptions\FileUploadException;

class EmailService
{
    private $mailer;
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config.php';
        $mailConfig = $this->config['mail'];

        $this->mailer = new PHPMailer(true);
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host       = $mailConfig['host'];
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $mailConfig['username'];
        $this->mailer->Password   = $mailConfig['password'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = $mailConfig['port'];

        // Sender
        $this->mailer->setFrom($mailConfig['from_address'], $mailConfig['from_name']);
    }

    public function sendSuccessNotificationToStudent(array $submissionData, string $pdfPath)
    {
        try {
            //Recipients
            $this->mailer->addAddress($submissionData['email'], $submissionData['nama_mahasiswa']);

            //Attachments
            $this->mailer->addAttachment($pdfPath, 'Tanda_Terima.pdf');

            //Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Konfirmasi Pengajuan Skripsi Berhasil';
            $this->mailer->Body    = "Halo " . htmlspecialchars($submissionData['nama_mahasiswa']) . ",<br><br>Pengajuan skripsi Anda dengan judul '<strong>" . htmlspecialchars($submissionData['judul_skripsi']) . "</strong>' telah berhasil kami terima.<br><br>Terlampir adalah tanda terima pengajuan Anda.<br><br>Terima kasih.";
            $this->mailer->AltBody = 'Pengajuan skripsi Anda telah berhasil kami terima. Tanda terima terlampir.';
            
            $this->mailer->send();
        } catch (PHPMailerException $e) {
            // Log error, but don't stop the user's flow.
            // In a real application, you would use a dedicated logger like Monolog.
            error_log("Mailer Error (to student): " . $this->mailer->ErrorInfo);
        }
    }

    public function sendSuccessNotificationToAdmin(array $submissionData)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            //Recipient
            $this->mailer->addAddress($this->config['mail']['admin_email']);

            //Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'New Thesis Submission: ' . htmlspecialchars($submissionData['nama_mahasiswa']);
            $this->mailer->Body    = "A new thesis submission has been received.<br><br>" .
                                     "<b>Student Name:</b> " . htmlspecialchars($submissionData['nama_mahasiswa']) . "<br>" .
                                     "<b>NIM:</b> " . htmlspecialchars($submissionData['nim']) . "<br>" .
                                     "<b>Title:</b> " . htmlspecialchars($submissionData['judul_skripsi']) . "<br><br>" .
                                     "Please log in to the admin dashboard to review it.";

            $this->mailer->send();
        } catch (PHPMailerException $e) {
            error_log("Mailer Error (to admin): " . $this->mailer->ErrorInfo);
        }
    }

    public function sendFailureNotificationToStudent(string $email, string $name, string $errorMessage)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($email, $name);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Submission Failed';
            $this->mailer->Body    = "Hello " . htmlspecialchars($name) . ",<br><br>We are sorry, but your thesis submission could not be processed due to an error: <br><i>" . htmlspecialchars($errorMessage) . "</i><br><br>Please try submitting again or contact support if the problem persists.";

            $this->mailer->send();
        } catch (PHPMailerException $e) {
            error_log("Mailer Error (failure to student): " . $this->mailer->ErrorInfo);
        }
    }

    /**
     * Send status update notification to student
     */
    public function sendStatusUpdateNotification(array $submissionData)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Generate submission letter PDF for approved submissions
            $pdfPath = null;
            if ($submissionData['status'] === 'Diterima') {
                $pdfService = new PdfService();
                $pdfPath = $pdfService->generateSubmissionLetter($submissionData);
            }

            //Recipients
            $this->mailer->addAddress($submissionData['email'], $submissionData['nama_mahasiswa']);

            // Attach PDF if it was generated
            if ($pdfPath && file_exists($pdfPath) && $submissionData['status'] === 'Diterima') {
                $this->mailer->addAttachment($pdfPath);
            }

            //Content
            $this->mailer->isHTML(true);
            
            if ($submissionData['status'] === 'Diterima') {
                $attachmentText = $pdfPath ? ' Berikut terlampir surat bukti unggah.' : '';
                $surveyLink = "https://forms.gle/pMTA5BH3nckq6SRb6";
                $this->mailer->Subject = 'Hasil Unggah Mandiri Skripsi';
                $this->mailer->Body = "Hello " . htmlspecialchars($submissionData['nama_mahasiswa']) . ",<br><br>" .
                                     "Hasil unggah mandiri skripsi Anda dengan judul '<strong>" . htmlspecialchars($submissionData['judul_skripsi']) . "</strong>' telah <strong>diterima</strong>.<br><br>" .
                                     "Selamat!" . $attachmentText."<br><br>".
                                     "Untuk membantu kami meningkatkan layanan, mohon luangkan waktu Anda untuk mengisi survei singkat melalui link berikut:<br>" .
             "<a href='" . $surveyLink . "'>Survei Kepuasan Sistem Unggah Mandiri Skripsi</a><br><br>" .
             "Terima kasih atas partisipasi Anda.";
                $this->mailer->AltBody = "Selamat!" . ($pdfPath ? ' Silakan lihat surat konfirmasi pengajuan yang terlampir.' : '');
            } else if ($submissionData['status'] === 'Ditolak') {
                $this->mailer->Subject = 'Hasil Unggah Mandiri Skripsi';
                // Check multiple possible fields for rejection reason
                $reason = '';
                if (!empty($submissionData['keterangan'])) {
                    $reason = htmlspecialchars($submissionData['keterangan']);
                } elseif (!empty($submissionData['reason'])) {
                    $reason = htmlspecialchars($submissionData['reason']);
                } elseif (!empty($submissionData['alasan'])) {
                    $reason = htmlspecialchars($submissionData['alasan']);
                }
                
                $reason = !empty($reason) ? $reason : 'Tidak ada alasan yang diberikan.';
                $this->mailer->Body = "Kepada " . htmlspecialchars($submissionData['nama_mahasiswa']) . ",<br><br>" .
                                     "Unggahan Skripsi Anda dengan judul '<strong>" . htmlspecialchars($submissionData['judul_skripsi']) . "</strong>' telah <strong>ditolak</strong>.<br><br>" .
                                     "Alasan: " . $reason . "<br><br>" .
                                     "Dimohon untuk mengunggah kembali skripsi Anda sesuai dengan format yang telah disetujui.";
                $this->mailer->AltBody = "Pengajuan skripsi Anda telah ditolak. Alasan: " . $reason;
            }

            $this->mailer->send();
            
            // Optionally delete the temporary file after sending
            // if ($pdfPath && file_exists($pdfPath)) {
            //     unlink($pdfPath);
            // }
        } catch (PHPMailerException $e) {
            error_log("Mailer Error (status update to student): " . $this->mailer->ErrorInfo);
        } catch (\Exception $e) {
            throw new FileUploadException("Error while sending status update notification: " . $e->getMessage());
        }
    }
}
