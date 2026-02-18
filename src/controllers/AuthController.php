<?php
/**
 * Auth Controller
 * 
 * Handles authentication endpoints (login, logout, user info)
 */

namespace HotelMaster\Controllers;

use HotelMaster\Core\Auth;
use HotelMaster\Core\Response;
use HotelMaster\Utils\Validator;

class AuthController {
    
    /**
     * POST /api/auth/login
     * Login user with email and password
     */
    public function login(array $params): array {
        try {
            // Get JSON body
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $validator = new Validator($input);
            $validator->required('email')->required('password');
            
            if ($validator->fails()) {
                return Response::validationError($validator->errors());
            }
            
            if (Auth::login($input['email'], $input['password'])) {
                $user = Auth::getCurrentUser();
                return Response::success([
                    'user' => $user,
                    'csrf_token' => Auth::getCsrfToken()
                ], 'Başarılı giriş');
            }
            
            return Response::error('E-posta veya şifre yanlış', 401);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/auth/user
     * Get current authenticated user
     */
    public function user(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized('Kimlik doğrulaması gerekli');
            }
            
            $user = Auth::getCurrentUser();
            return Response::success([
                'user' => $user,
                'csrf_token' => Auth::getCsrfToken()
            ]);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/auth/logout
     * Logout user
     */
    public function logout(array $params): array {
        try {
            Auth::logout();
            return Response::success(null, 'Başarılı çıkış');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/auth/change-password
     * Change user password
     */
    public function changePassword(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $validator = new Validator($input);
            $validator->required('current_password')
                     ->required('new_password')
                     ->minLength('new_password', 8);
            
            if ($validator->fails()) {
                return Response::validationError($validator->errors());
            }
            
            $user = Auth::getCurrentUser();
            
            // Verify current password
            if (!Auth::verifyPassword($input['current_password'], $user['password_hash'])) {
                return Response::error('Mevcut şifre yanlış', 400);
            }
            
            // Update password
            \HotelMaster\Models\User::update(
                $user['id'],
                ['password' => $input['new_password']]
            );
            
            \HotelMaster\Core\Logger::audit('password_changed', 'user', $user['id']);
            
            return Response::success(null, 'Şifre başarılı değiştirildi');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
