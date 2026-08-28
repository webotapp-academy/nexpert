<?php
// Email Helper using PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/universal-env.php';

class EmailHelper {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // SMTP Configuration using universal env loader
        $this->mail->isSMTP();
        $this->mail->Host = UniversalEnv::get('SMTP_HOST', 'smtp.gmail.com');
        $this->mail->SMTPAuth = true;
        $this->mail->Username = UniversalEnv::get('SMTP_USERNAME');
        $this->mail->Password = UniversalEnv::get('SMTP_PASSWORD');
        $this->mail->Timeout = 10;
        
        // SSL Options for local/production environments
        $this->mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        // Use SSL for port 465, STARTTLS for port 587
        $port = (int)UniversalEnv::get('SMTP_PORT', 587);
        $this->mail->Port = $port;
        if ($port === 465) {
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        } else {
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
        }
        
        // From address
        $fromEmail = UniversalEnv::get('SMTP_FROM_EMAIL', 'support@nexpert.ai');
        $fromName = UniversalEnv::get('SMTP_FROM_NAME', 'Nexpert.ai');
        $this->mail->setFrom($fromEmail, $fromName);
    }
    
    // Generic send email method
    public function sendEmail($toEmail, $subject, $htmlBody, $toName = '') {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $toName);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $htmlBody;
            
            $this->mail->send();
            return ['success' => true, 'message' => 'Email sent successfully'];
            
        } catch (Exception $e) {
            error_log("Email send failed: " . $this->mail->ErrorInfo);
            return ['success' => false, 'error' => $this->mail->ErrorInfo];
        }
    }
    
    // Send booking confirmation email to learner
    public function sendLearnerBookingEmail($learnerEmail, $learnerName, $expertName, $sessionTopic, $sessionDate, $sessionTime, $duration, $zoomLink, $zoomPassword) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($learnerEmail, $learnerName);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = "Booking Confirmed: {$sessionTopic} with {$expertName}";
            
            $this->mail->Body = $this->getLearnerEmailTemplate(
                $learnerName, 
                $expertName, 
                $sessionTopic, 
                $sessionDate, 
                $sessionTime, 
                $duration, 
                $zoomLink, 
                $zoomPassword
            );
            
            $this->mail->send();
            return ['success' => true, 'message' => 'Email sent to learner'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $this->mail->ErrorInfo];
        }
    }
    
    // Send booking confirmation email to expert (After Acceptance)
    public function sendExpertBookingEmail($expertEmail, $expertName, $learnerName, $sessionTopic, $sessionDate, $sessionTime, $duration, $zoomLink, $zoomPassword) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($expertEmail, $expertName);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = "Session Confirmed: {$sessionTopic} with {$learnerName}";
            
            $this->mail->Body = $this->getExpertEmailTemplate(
                $expertName, 
                $learnerName, 
                $sessionTopic, 
                $sessionDate, 
                $sessionTime, 
                $duration, 
                $zoomLink, 
                $zoomPassword
            );
            
            $this->mail->send();
            return ['success' => true, 'message' => 'Email sent to expert'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $this->mail->ErrorInfo];
        }
    }

    // Send instant notification email to expert when a learner FIRST books a session
    public function sendExpertNewBookingRequestAlert($expertEmail, $expertName, $learnerName, $sessionTopic, $sessionDate, $sessionTime, $duration, $amount) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($expertEmail, $expertName);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = "🔔 New Booking Request: {$learnerName} booked a session with you";
            
            $this->mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #0f172a 0%, #00D4AA 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                    .detail-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #00D4AA; }
                    .action-button { display: inline-block; background: #00D4AA; color: #080B10; padding: 14px 28px; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: bold; }
                    .footer { text-align: center; color: #6b7280; margin-top: 30px; font-size: 14px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🚀 New Booking Request</h1>
                    </div>
                    <div class='content'>
                        <p>Hi <strong>{$expertName}</strong>,</p>
                        <p><strong>{$learnerName}</strong> has just scheduled a 1-on-1 session with you on Nexpert!</p>
                        
                        <div class='detail-box'>
                            <h3 style='margin-top: 0;'>📅 Session Details</h3>
                            <p><strong>Topic:</strong> {$sessionTopic}</p>
                            <p><strong>Learner:</strong> {$learnerName}</p>
                            <p><strong>Date:</strong> {$sessionDate}</p>
                            <p><strong>Time:</strong> {$sessionTime}</p>
                            <p><strong>Duration:</strong> {$duration} minutes</p>
                            <p><strong>Session Fee:</strong> ₹{$amount}</p>
                        </div>
                        
                        <p>Please log in to your expert dashboard to accept or manage this booking:</p>
                        <div style='text-align: center;'>
                            <a href='http://localhost/nexpert/index.php?panel=expert&page=booking-management' class='action-button'>Go to Booking Management</a>
                        </div>
                        
                        <div class='footer'>
                            <p>Best regards,<br>The Nexpert.ai Team</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $this->mail->send();
            return ['success' => true, 'message' => 'New booking alert sent to expert'];
        } catch (Exception $e) {
            error_log("Failed to send new booking alert to expert: " . $this->mail->ErrorInfo);
            return ['success' => false, 'error' => $this->mail->ErrorInfo];
        }
    }
    
    // Learner email template
    private function getLearnerEmailTemplate($learnerName, $expertName, $topic, $date, $time, $duration, $zoomLink, $password) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                .detail-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #f59e0b; }
                .zoom-button { display: inline-block; background: #2563eb; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: bold; }
                .footer { text-align: center; color: #6b7280; margin-top: 30px; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Your Session is Confirmed!</h1>
                </div>
                <div class='content'>
                    <p>Hi <strong>{$learnerName}</strong>,</p>
                    <p>Great news! Your session with <strong>{$expertName}</strong> has been confirmed.</p>
                    
                    <div class='detail-box'>
                        <h3>📅 Session Details</h3>
                        <p><strong>Topic:</strong> {$topic}</p>
                        <p><strong>Date:</strong> {$date}</p>
                        <p><strong>Time:</strong> {$time} IST</p>
                        <p><strong>Duration:</strong> {$duration} minutes</p>
                        <p><strong>Expert:</strong> {$expertName}</p>
                    </div>
                    
                    <div class='detail-box'>
                        <h3>🎥 Zoom Meeting Details</h3>
                        <p><strong>Meeting Password:</strong> {$password}</p>
                        <a href='{$zoomLink}' class='zoom-button'>Join Zoom Meeting</a>
                        <p style='font-size: 12px; color: #6b7280;'>Meeting Link: {$zoomLink}</p>
                    </div>
                    
                    <p>💡 <strong>Tips for a great session:</strong></p>
                    <ul>
                        <li>Join 5 minutes early to test your audio and video</li>
                        <li>Be in a quiet space with good lighting</li>
                        <li>Prepare any questions or topics you want to discuss</li>
                    </ul>
                    
                    <div class='footer'>
                        <p>Best regards,<br>The Nexpert.ai Team</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    // Expert email template
    private function getExpertEmailTemplate($expertName, $learnerName, $topic, $date, $time, $duration, $zoomLink, $password) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                .detail-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #f59e0b; }
                .zoom-button { display: inline-block; background: #059669; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: bold; }
                .footer { text-align: center; color: #6b7280; margin-top: 30px; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📚 New Session Booked!</h1>
                </div>
                <div class='content'>
                    <p>Hi <strong>{$expertName}</strong>,</p>
                    <p>You have a new confirmed session with <strong>{$learnerName}</strong>.</p>
                    
                    <div class='detail-box'>
                        <h3>📅 Session Details</h3>
                        <p><strong>Topic:</strong> {$topic}</p>
                        <p><strong>Date:</strong> {$date}</p>
                        <p><strong>Time:</strong> {$time} IST</p>
                        <p><strong>Duration:</strong> {$duration} minutes</p>
                        <p><strong>Learner:</strong> {$learnerName}</p>
                    </div>
                    
                    <div class='detail-box'>
                        <h3>🎥 Zoom Meeting Details (Host Link)</h3>
                        <p><strong>Meeting Password:</strong> {$password}</p>
                        <a href='{$zoomLink}' class='zoom-button'>Start as Host (Click to Launch Meeting)</a>
                        <p style='font-size: 12px; color: #6b7280;'>⚠️ This is your host link - use this to start and control the meeting</p>
                        <p style='font-size: 11px; color: #9ca3af; margin-top: 5px;'>Host Link: {$zoomLink}</p>
                    </div>
                    
                    <p>💡 <strong>Session preparation:</strong></p>
                    <ul>
                        <li>Review the session topic and learner's background if available</li>
                        <li>Prepare any materials or resources you plan to share</li>
                        <li>Join the meeting a few minutes early</li>
                    </ul>
                    
                    <div class='footer'>
                        <p>Best regards,<br>The Nexpert.ai Team</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    // Send withdrawal/payout initiated email to expert
    public function sendWithdrawalInitiatedEmail($expertEmail, $expertName, $amount, $bankAccountLast4) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($expertEmail, $expertName);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = "Withdrawal Initiated - ₹" . number_format($amount, 2);
            
            $this->mail->Body = $this->getWithdrawalInitiatedTemplate($expertName, $amount, $bankAccountLast4);
            
            $this->mail->send();
            return ['success' => true, 'message' => 'Withdrawal email sent'];
            
        } catch (Exception $e) {
            error_log("Withdrawal Email Error: " . $this->mail->ErrorInfo);
            return ['success' => false, 'error' => $this->mail->ErrorInfo];
        }
    }
    
    // Send withdrawal completed email to expert
    public function sendWithdrawalCompletedEmail($expertEmail, $expertName, $amount, $transactionId, $bankAccountLast4) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($expertEmail, $expertName);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = "Withdrawal Completed - ₹" . number_format($amount, 2);
            
            $this->mail->Body = $this->getWithdrawalCompletedTemplate($expertName, $amount, $transactionId, $bankAccountLast4);
            
            $this->mail->send();
            return ['success' => true, 'message' => 'Withdrawal completion email sent'];
            
        } catch (Exception $e) {
            error_log("Withdrawal Completion Email Error: " . $this->mail->ErrorInfo);
            return ['success' => false, 'error' => $this->mail->ErrorInfo];
        }
    }
    
    // Withdrawal Initiated Email Template
    private function getWithdrawalInitiatedTemplate($expertName, $amount, $bankAccountLast4) {
        $formattedAmount = number_format($amount, 2);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #F59E0B 0%, #EF4444 100%); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 30px; }
                .amount-box { background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 20px; margin: 20px 0; border-radius: 5px; text-align: center; }
                .amount-box .amount { font-size: 32px; font-weight: bold; color: #F59E0B; margin: 10px 0; }
                .detail-box { background: #f9fafb; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #e5e7eb; }
                .detail-box h3 { margin-top: 0; color: #F59E0B; }
                .detail-box p { margin: 8px 0; }
                .info-banner { background: #DBEAFE; border-left: 4px solid #3B82F6; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
                ul { padding-left: 20px; }
                li { margin: 8px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>💸 Withdrawal Initiated</h1>
                </div>
                <div class='content'>
                    <p>Hi {$expertName},</p>
                    
                    <p>Your withdrawal request has been initiated successfully. The funds will be transferred to your registered bank account shortly.</p>
                    
                    <div class='amount-box'>
                        <p style='margin: 0; font-size: 14px; color: #78716c;'>Amount</p>
                        <div class='amount'>₹{$formattedAmount}</div>
                    </div>
                    
                    <div class='detail-box'>
                        <h3>📋 Transfer Details</h3>
                        <p><strong>Bank Account:</strong> ****{$bankAccountLast4}</p>
                        <p><strong>Processing Time:</strong> 1-2 business days</p>
                        <p><strong>Status:</strong> <span style='color: #F59E0B; font-weight: bold;'>Processing</span></p>
                    </div>
                    
                    <div class='info-banner'>
                        <p style='margin: 0;'><strong>ℹ️ What happens next?</strong></p>
                        <p style='margin: 5px 0 0 0;'>Your withdrawal request is being processed. It usually takes about 1-2 business days for the transfer to reflect in your account.</p>
                    </div>
                    
                    <p><strong>Important Notes:</strong></p>
                    <ul>
                        <li>You will receive a confirmation email once the transfer is completed</li>
                        <li>The actual transfer time may vary depending on your bank</li>
                        <li>In case of any discrepancies or issues, please reply to this email</li>
                    </ul>
                    
                    <div class='footer'>
                        <p><strong>Cheers,<br>Team Nexpert.ai</strong></p>
                        <p style='margin-top: 10px; color: #9ca3af;'>Copyright ©2025 Nexpert.ai. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    // Withdrawal Completed Email Template
    private function getWithdrawalCompletedTemplate($expertName, $amount, $transactionId, $bankAccountLast4) {
        $formattedAmount = number_format($amount, 2);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 30px; }
                .success-icon { text-align: center; font-size: 64px; margin: 20px 0; }
                .amount-box { background: #D1FAE5; border-left: 4px solid #10B981; padding: 20px; margin: 20px 0; border-radius: 5px; text-align: center; }
                .amount-box .amount { font-size: 32px; font-weight: bold; color: #10B981; margin: 10px 0; }
                .detail-box { background: #f9fafb; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #e5e7eb; }
                .detail-box h3 { margin-top: 0; color: #10B981; }
                .detail-box p { margin: 8px 0; }
                .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Withdrawal Completed</h1>
                </div>
                <div class='content'>
                    <div class='success-icon'>✓</div>
                    
                    <p>Hi {$expertName},</p>
                    
                    <p>Great news! Your withdrawal has been processed successfully and the funds have been transferred to your bank account.</p>
                    
                    <div class='amount-box'>
                        <p style='margin: 0; font-size: 14px; color: #78716c;'>Amount Transferred</p>
                        <div class='amount'>₹{$formattedAmount}</div>
                    </div>
                    
                    <div class='detail-box'>
                        <h3>📋 Transaction Details</h3>
                        <p><strong>Transaction ID:</strong> {$transactionId}</p>
                        <p><strong>Bank Account:</strong> ****{$bankAccountLast4}</p>
                        <p><strong>Date:</strong> " . date('d M Y, h:i A') . "</p>
                        <p><strong>Status:</strong> <span style='color: #10B981; font-weight: bold;'>✓ Completed</span></p>
                    </div>
                    
                    <p><strong>💡 Please Note:</strong></p>
                    <p>It may take a few hours for the funds to reflect in your bank account depending on your bank's processing time.</p>
                    
                    <p>If you don't see the credit within 24 hours or have any questions about this transaction, please contact us.</p>
                    
                    <div class='footer'>
                        <p><strong>Cheers,<br>Team Nexpert.ai</strong></p>
                        <p style='margin-top: 10px; color: #9ca3af;'>Copyright ©2025 Nexpert.ai. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
