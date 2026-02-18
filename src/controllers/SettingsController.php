<?php
/**
 * Settings Controller
 * 
 * Handles system settings and admin functions
 */

namespace HotelMaster\Controllers;

use HotelMaster\Core\Auth;
use HotelMaster\Core\Response;
use HotelMaster\Core\Logger;
use HotelMaster\Models\Setting;
use HotelMaster\Utils\FileManager;
use HotelMaster\Utils\Validator;

class SettingsController {
    
    /**
     * GET /api/settings
     * Get all settings
     */
    public function index(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('settings.view');
            
            $settings = Setting::all();
            
            return Response::success($settings, 'Ayarlar başarıyla alındı');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * PUT /api/settings
     * Update settings
     */
    public function update(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('settings.edit');
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            foreach ($input as $key => $value) {
                // Whitelist settings that can be changed
                $allowedSettings = ['hotel_name', 'currency', 'currency_symbol', 'check_in_time', 'check_out_time'];
                
                if (in_array($key, $allowedSettings)) {
                    $type = gettype($value);
                    Setting::set($key, $value, $type);
                    Logger::audit('setting_changed', 'setting', null, ['key' => $key], ['key' => $key, 'value' => $value]);
                }
            }
            
            $settings = Setting::all();
            return Response::success($settings, 'Ayarlar başarılı güncellendi');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/settings/{key}
     * Get single setting
     */
    public function show(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('settings.view');
            
            $value = Setting::get($params['key']);
            
            if ($value === null) {
                return Response::notFound('Ayar bulunamadı');
            }
            
            return Response::success(['key' => $params['key'], 'value' => $value]);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/settings/backup
     * Create database backup
     */
    public function backup(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('settings.backup');
            
            $backupPath = FileManager::backupDatabase();
            
            if (!$backupPath) {
                return Response::error('Yedek oluşturulamadı', 400);
            }
            
            Logger::audit('backup_created', 'system');
            
            return Response::success([
                'backup_path' => $backupPath,
                'filename' => basename($backupPath),
                'size' => FileManager::size($backupPath)
            ], 'Yedek başarılı oluşturuldu');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/settings/restore
     * Restore from backup
     */
    public function restore(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('settings.restore');
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $validator = new Validator($input);
            $validator->required('backup_file');
            
            if ($validator->fails()) {
                return Response::validationError($validator->errors());
            }
            
            $backupPath = BACKUPS_PATH . '/' . basename($input['backup_file']);
            
            if (!file_exists($backupPath)) {
                return Response::error('Yedek dosyası bulunamadı', 404);
            }
            
            if (!FileManager::restoreDatabase($backupPath)) {
                return Response::error('Geri yükleme başarısız', 400);
            }
            
            Logger::audit('backup_restored', 'system');
            
            return Response::success(null, 'Yedekten geri yükleme başarılı');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/settings/backups
     * List all backups
     */
    public function listBackups(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('settings.view');
            
            $backups = FileManager::listFiles(BACKUPS_PATH);
            
            return Response::success($backups, 'Yedekler listelendi');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
