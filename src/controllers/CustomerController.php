<?php
/**
 * Customer Controller
 * 
 * Handles customer management endpoints
 */

namespace HotelMaster\Controllers;

use HotelMaster\Core\Auth;
use HotelMaster\Core\Response;
use HotelMaster\Core\Logger;
use HotelMaster\Models\Customer;
use HotelMaster\Utils\Validator;

class CustomerController {
    
    /**
     * GET /api/customers
     * Get all customers
     */
    public function list(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('customers.view');
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = ITEMS_PER_PAGE;
            $offset = ($page - 1) * $perPage;
            
            $customers = Customer::all($perPage, $offset);
            $total = Customer::count();
            
            return Response::paginated($customers, $total, $page, $perPage, 'Müşteriler başarıyla alındı');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/customers/{id}
     * Get single customer
     */
    public function show(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('customers.view');
            
            $customer = Customer::find($params['id']);
            
            if (!$customer) {
                return Response::notFound('Müşteri bulunamadı');
            }
            
            return Response::success($customer);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/customers
     * Create new customer
     */
    public function store(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('customers.create');
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $validator = new Validator($input);
            $validator->required('first_name')
                     ->required('last_name')
                     ->required('phone');
            
            if ($validator->fails()) {
                return Response::validationError($validator->errors());
            }
            
            $customerId = Customer::create($input);
            
            if (!$customerId) {
                return Response::error('Müşteri oluşturulamadı', 400);
            }
            
            Logger::audit('customer_created', 'customer', $customerId, null, $input);
            
            $customer = Customer::find($customerId);
            return Response::created($customer, 'Müşteri başarılı oluşturuldu');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * PUT /api/customers/{id}
     * Update customer
     */
    public function update(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('customers.edit');
            
            $customer = Customer::find($params['id']);
            if (!$customer) {
                return Response::notFound('Müşteri bulunamadı');
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            if (Customer::update($params['id'], $input)) {
                Logger::audit('customer_updated', 'customer', $params['id'], $customer, $input);
                
                $updatedCustomer = Customer::find($params['id']);
                return Response::success($updatedCustomer, 'Müşteri başarılı güncellendi');
            }
            
            return Response::error('Müşteri güncellenemedi', 400);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * DELETE /api/customers/{id}
     * Delete customer
     */
    public function delete(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('customers.delete');
            
            $customer = Customer::find($params['id']);
            if (!$customer) {
                return Response::notFound('Müşteri bulunamadı');
            }
            
            if (Customer::delete($params['id'])) {
                Logger::audit('customer_deleted', 'customer', $params['id'], $customer);
                return Response::success(null, 'Müşteri başarılı silindi');
            }
            
            return Response::error('Müşteri silinemedi', 400);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/customers/search
     * Search customers
     */
    public function search(array $params): array {
        try {
            if (!Auth::isAuthenticated()) {
                return Response::unauthorized();
            }
            
            Auth::requirePermission('customers.view');
            
            $query = $_GET['q'] ?? '';
            
            if (strlen($query) < 2) {
                return Response::error('Arama sorgusu en az 2 karakter olmalı', 400);
            }
            
            $customers = Customer::search($query);
            
            return Response::success($customers, 'Arama sonuçları');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
