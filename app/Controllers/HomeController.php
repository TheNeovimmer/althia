<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\BlogPost;
use App\Models\Appointment;

class HomeController extends Controller
{
    public function index(): void
    {
        $doctors = Doctor::getVerified();
        $services = Service::getActive();
        $posts = BlogPost::getRecent(3);
        $stats = [
            'happy_clients' => '4K+',
            'providers' => '650',
            'appointments_booked' => '15K+',
            'specialists' => '55+',
        ];

        $this->render('public/home', compact('doctors', 'services', 'posts', 'stats'));
    }

    public function about(): void
    {
        $this->render('public/about');
    }

    public function services(): void
    {
        $services = Service::getActive();
        $this->render('public/services', compact('services'));
    }

    public function experts(): void
    {
        $doctors = Doctor::getVerified();
        $this->render('public/experts', compact('doctors'));
    }

    public function contact(): void
    {
        $this->render('public/contact');
    }

    public function sendContact(): void
    {
        $body = $this->getBody();

        $db = \App\Core\Database::getInstance();
        $db->insert(
            "INSERT INTO contacts (first_name, last_name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?, ?)",
            [$body['first_name'] ?? '', $body['last_name'] ?? '', $body['email'] ?? '', $body['phone'] ?? '', $body['subject'] ?? '', $body['message'] ?? '']
        );

        $_SESSION['_flash']['success'] = 'Thank you for your message. We will get back to you shortly.';
        $this->redirect('/contact');
    }

    public function pricing(): void
    {
        $this->render('public/pricing');
    }

    public function notFound(): void
    {
        http_response_code(404);
        $this->render('public/404');
    }
}
