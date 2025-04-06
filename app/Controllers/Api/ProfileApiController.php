<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ProfileApiController extends ApiBaseController
{
    protected $userModel;
    protected $participantModel;

    /**
     * Initialize controller, set models
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);

        // Initialize models
        $this->userModel = new \App\Models\UserModel();
        $this->participantModel = new \App\Models\ParticipantModel();

        // Load the storage helper
        helper(['storage']);
    }

    /**
     * Upload a profile picture for a participant
     *
     * @param int $participantId
     * @return ResponseInterface
     */
    public function uploadParticipantProfilePicture($participantId = null)
    {
        try {
            if (!$participantId) {
                return $this->respondError('Participant ID is required', self::HTTP_BAD_REQUEST);
            }

            // Verify participant exists
            $participant = $this->participantModel->find($participantId);
            if (!$participant) {
                return $this->respondError('Participant not found', self::HTTP_NOT_FOUND);
            }

            // Check if file uploaded
            if (empty($_FILES['profile_picture'])) {
                return $this->respondError('No profile picture file uploaded', self::HTTP_BAD_REQUEST);
            }

            // Upload profile picture using helper function
            $uploadResult = upload_profile_picture($_FILES['profile_picture'], $participantId);

            if (!$uploadResult['status']) {
                return $this->respondError($uploadResult['message'], self::HTTP_BAD_REQUEST);
            }

            // If participant has a previous profile picture, delete it
            if (!empty($participant->profile_picture)) {
                // Extract the path from the full URL
                $oldPath = parse_url($participant->profile_picture, PHP_URL_PATH);
                if ($oldPath) {
                    delete_storage_file($oldPath);
                }
            }

            // Update participant's profile picture in database
            $this->participantModel->update($participantId, [
                'profile_picture' => $uploadResult['url']
            ]);

            return $this->respondSuccess([
                'profile_picture_url' => $uploadResult['url']
            ], self::HTTP_OK, 'Profile picture uploaded successfully');
        } catch (\Exception $e) {
            return $this->respondError('An error occurred: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }
}