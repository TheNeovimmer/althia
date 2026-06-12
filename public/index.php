<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Middleware;

$router = new Router();

// Public routes
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/services', 'HomeController@services');
$router->get('/experts', 'HomeController@experts');
$router->get('/blog', 'BlogController@index');
$router->get('/blog/{slug}', 'BlogController@show');
$router->get('/contact', 'HomeController@contact');
$router->post('/contact', 'HomeController@sendContact');
$router->get('/pricing', 'HomeController@pricing');

// Auth routes
$router->get('/login', 'AuthController@loginForm', [Middleware::guest()]);
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@registerForm', [Middleware::guest()]);
$router->post('/register', 'AuthController@register');
$router->get('/forgot-password', 'AuthController@forgotForm', [Middleware::guest()]);
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/logout', 'AuthController@logout', [Middleware::auth()]);

// Patient routes
$router->get('/patient/dashboard', 'PatientController@dashboard', [Middleware::patient()]);
$router->get('/patient/profile', 'PatientController@profile', [Middleware::patient()]);
$router->post('/patient/profile', 'PatientController@profile', [Middleware::patient()]);
$router->get('/patient/records', 'PatientController@records', [Middleware::patient()]);
$router->post('/patient/upload', 'PatientController@uploadReport', [Middleware::patient()]);
$router->get('/patient/appointments', 'PatientController@appointments', [Middleware::patient()]);
$router->get('/patient/appointments/create', 'PatientController@appointmentsForm', [Middleware::patient()]);
$router->post('/patient/appointments/create', 'PatientController@createAppointment', [Middleware::patient()]);
$router->post('/patient/appointments/{id}/cancel', 'PatientController@cancelAppointment', [Middleware::patient()]);
$router->get('/patient/prescriptions', 'PatientController@prescriptions', [Middleware::patient()]);

// Doctor routes
$router->get('/doctor/dashboard', 'DoctorController@dashboard', [Middleware::doctor()]);
$router->get('/doctor/profile', 'DoctorController@profile', [Middleware::doctor()]);
$router->post('/doctor/profile', 'DoctorController@profile', [Middleware::doctor()]);
$router->get('/doctor/patients', 'DoctorController@patients', [Middleware::doctor()]);
$router->get('/doctor/patients/{id}', 'DoctorController@patientDetail', [Middleware::doctor()]);
$router->get('/doctor/prescriptions', 'DoctorController@prescriptions', [Middleware::doctor()]);
$router->get('/doctor/prescriptions/create', 'DoctorController@prescriptionsForm', [Middleware::doctor()]);
$router->get('/doctor/prescriptions/create/{id}', 'DoctorController@prescriptionsForm', [Middleware::doctor()]);
$router->post('/doctor/prescriptions/create', 'DoctorController@createPrescription', [Middleware::doctor()]);
$router->get('/doctor/records/create', 'DoctorController@recordsForm', [Middleware::doctor()]);
$router->get('/doctor/records/create/{id}', 'DoctorController@recordsForm', [Middleware::doctor()]);
$router->post('/doctor/records/create', 'DoctorController@createReport', [Middleware::doctor()]);
$router->get('/doctor/appointments', 'DoctorController@appointments', [Middleware::doctor()]);
$router->get('/doctor/appointments/{id}/complete', 'DoctorController@completeAppointment', [Middleware::doctor()]);
$router->get('/doctor/appointments/{id}/cancel', 'DoctorController@cancelAppointment', [Middleware::doctor()]);
$router->post('/doctor/appointments/{id}/update', 'DoctorController@updateAppointment', [Middleware::doctor()]);

// Admin routes
$router->get('/admin/dashboard', 'AdminController@dashboard', [Middleware::admin()]);
$router->get('/admin/users', 'AdminController@users', [Middleware::admin()]);
$router->post('/admin/users', 'AdminController@createUser', [Middleware::admin()]);
$router->post('/admin/users/{id}/update', 'AdminController@updateUser', [Middleware::admin()]);
$router->post('/admin/users/{id}/delete', 'AdminController@deleteUser', [Middleware::admin()]);
$router->get('/admin/doctors', 'AdminController@doctors', [Middleware::admin()]);
$router->get('/admin/doctors/create', 'AdminController@createDoctorForm', [Middleware::admin()]);
$router->post('/admin/doctors/create', 'AdminController@createDoctor', [Middleware::admin()]);
$router->get('/admin/doctors/{id}/edit', 'AdminController@editDoctorForm', [Middleware::admin()]);
$router->post('/admin/doctors/{id}/edit', 'AdminController@updateDoctor', [Middleware::admin()]);
$router->post('/admin/doctors/{id}/verify', 'AdminController@verifyDoctor', [Middleware::admin()]);
$router->get('/admin/doctors/{id}/toggle-status', 'AdminController@toggleDoctorStatus', [Middleware::admin()]);
$router->get('/admin/users/{id}/toggle-status', 'AdminController@toggleUserStatus', [Middleware::admin()]);
$router->get('/admin/profile', 'AdminController@profile', [Middleware::admin()]);
$router->post('/admin/profile', 'AdminController@profile', [Middleware::admin()]);

// Admin extra routes
$router->get('/admin/appointments', 'AdminController@appointments', [Middleware::admin()]);
$router->post('/admin/appointments/{id}/confirm', 'AdminController@confirmAppointment', [Middleware::admin()]);
$router->post('/admin/appointments/{id}/cancel', 'AdminController@cancelAppointment', [Middleware::admin()]);
$router->get('/admin/blog', 'AdminController@blog', [Middleware::admin()]);
$router->get('/admin/blog/create', 'AdminController@createBlogForm', [Middleware::admin()]);
$router->post('/admin/blog/create', 'AdminController@createBlogPost', [Middleware::admin()]);
$router->get('/admin/blog/{id}/edit', 'AdminController@editBlogForm', [Middleware::admin()]);
$router->post('/admin/blog/{id}/edit', 'AdminController@updateBlogPost', [Middleware::admin()]);
$router->post('/admin/blog/{id}/delete', 'AdminController@deleteBlogPost', [Middleware::admin()]);
$router->get('/admin/blog/categories', 'AdminController@blogCategories', [Middleware::admin()]);
$router->post('/admin/blog/categories', 'AdminController@saveCategory', [Middleware::admin()]);
$router->get('/admin/contacts', 'AdminController@contacts', [Middleware::admin()]);
$router->post('/admin/contacts/{id}/mark-read', 'AdminController@markContactRead', [Middleware::admin()]);
$router->post('/admin/contacts/{id}/delete', 'AdminController@deleteContact', [Middleware::admin()]);
$router->get('/admin/notifications', 'AdminController@notifications', [Middleware::admin()]);
$router->post('/admin/notifications/mark-all-read', 'AdminController@markAllNotificationsRead', [Middleware::admin()]);

// Doctor extra routes
$router->get('/doctor/messages', 'DoctorController@messages', [Middleware::doctor()]);
$router->get('/doctor/messages/conversation/{id}', 'DoctorController@conversation', [Middleware::doctor()]);
$router->post('/doctor/messages/send', 'DoctorController@sendMessage', [Middleware::doctor()]);
$router->get('/doctor/notifications', 'DoctorController@notifications', [Middleware::doctor()]);
$router->post('/doctor/notifications/mark-all-read', 'DoctorController@markAllNotificationsRead', [Middleware::doctor()]);
$router->get('/doctor/availability', 'DoctorController@availability', [Middleware::doctor()]);
$router->post('/doctor/availability', 'DoctorController@saveAvailability', [Middleware::doctor()]);

// Patient extra routes
$router->get('/patient/messages', 'PatientController@messages', [Middleware::patient()]);
$router->get('/patient/messages/conversation/{id}', 'PatientController@conversation', [Middleware::patient()]);
$router->post('/patient/messages/send', 'PatientController@sendMessage', [Middleware::patient()]);
$router->get('/patient/notifications', 'PatientController@notifications', [Middleware::patient()]);
$router->post('/patient/notifications/mark-all-read', 'PatientController@markAllNotificationsRead', [Middleware::patient()]);
$router->post('/patient/reports/upload', 'PatientController@uploadReport', [Middleware::patient()]);

// AI routes
$router->post('/api/ai/chat', 'AIController@chat', [Middleware::auth()]);
$router->post('/api/ai/public-chat', 'AIController@publicChat');
$router->post('/api/ai/symptoms', 'AIController@analyzeSymptoms', [Middleware::auth()]);

// 404 route
$router->get('/404', 'HomeController@notFound');

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
