<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\ApiBaseController;
use App\Models\ParticipantModel;

class NotificationsApiController extends ApiBaseController
{
    protected $model;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->model = new ParticipantModel();
    }

    /**
     * 🔔 Generate Random Registration Notifications for a Program
     * GET /api/notifications/random-registration?web_url={web_url}
     * 
     * Generates notification data for random registrations in a specific program
     */
    public function generateRandomRegistrationNotifications()
    {
        $webUrl = $this->request->getGet('web_url');

        // Validate web URL
        if ($webUrl === null) {
            return $this->respondValidationErrors("Valid web URL is required.");
        }

        try {
            // Get random participant for the program
                $participant = $this->model->getRandomParticipantForProgram($webUrl);

            if (empty($participant)) {
                return $this->respondNotFound("No participants found for the program.");
            }

            // Format data as notifications
            $registrationPhrases = [
                "🎉 {full_name} from {nationality} just joined the program! Welcome aboard!",
                "New registration alert! {full_name} ({nationality}) is now part of our community.",
                "📢 Exciting news! {full_name} from {nationality} has signed up for the program.",
                "The team from {nationality} is growing! {full_name} has registered.",
                "🚀 {full_name} ({nationality}) is our newest participant. Let's welcome them!",
                "Welcome to the journey! {full_name} from {nationality} has joined us.",
                "🌟 Another star from {nationality}! {full_name} is now registered.",
                "Breaking news! {full_name} ({nationality}) has enrolled in the program.",
                "🎯 {full_name} from {nationality} took the leap and joined our program today!",
                "We're thrilled to have {full_name} ({nationality}) on board with us!",
                "🌍 Our global community grows! {full_name} from {nationality} has joined.",
                "✨ {full_name} ({nationality}) just became part of our program family!",
                "A warm welcome to {full_name} from {nationality} for joining our initiative!",
                "🎊 Program update: {full_name} ({nationality}) is our latest member.",
                "New talent alert! {full_name} from {nationality} has registered with us."
            ];

            $randomPhrase = $registrationPhrases[array_rand($registrationPhrases)];
            $message = str_replace(
                ['{full_name}', '{nationality}'],
                [$participant->full_name, $participant->nationality],
                $randomPhrase
            );

            return $this->respondSuccess($message, self::HTTP_OK, "Random registration notifications generated successfully.");
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage(), self::HTTP_INTERNAL_ERROR, "Failed to generate notifications.");
        }
    }

    
}
