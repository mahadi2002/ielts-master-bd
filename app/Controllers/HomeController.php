<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\ContactRepo;
use App\Services\AuditService;
use App\Services\Notifier;

final class HomeController extends Controller
{
    private const CONTACT_STARTED_KEY = '_contact_form_started';

    public function index(Request $request): Response
    {
        $wordOfTheDay = Db::first("SELECT * FROM words WHERE is_exclusive = 0 ORDER BY RAND() LIMIT 1");

        $sampleQuiz = Db::first(
            "SELECT q.*, w.headword FROM quizzes q JOIN words w ON w.id = q.word_id
              WHERE q.quiz_type = 'mcq' ORDER BY RAND() LIMIT 1"
        );
        if ($sampleQuiz && $sampleQuiz['options']) {
            $sampleQuiz['options'] = json_decode((string) $sampleQuiz['options'], true);
        }

        return $this->view('home/index', [
            'wordOfTheDay' => $wordOfTheDay,
            'sampleQuiz'   => $sampleQuiz,
        ]);
    }

    public function privacy(Request $request): Response
    {
        return $this->view('home/privacy', ['title' => 'Privacy Policy']);
    }

    public function terms(Request $request): Response
    {
        return $this->view('home/terms', ['title' => 'Terms & Conditions']);
    }

    public function about(Request $request): Response
    {
        return $this->view('home/about', ['title' => 'About Us']);
    }

    public function contact(Request $request): Response
    {
        Session::put(self::CONTACT_STARTED_KEY, time());
        return $this->view('home/contact', ['title' => 'Contact Us']);
    }

    public function submitContact(Request $request): Response
    {
        // Honeypot — a real visitor never fills a field hidden by CSS.
        if ($request->str('website') !== '') {
            Session::notify('success', 'বার্তা পেয়েছি। আমরা শীঘ্রই যোগাযোগ করব।');
            return $this->redirect('/contact');
        }

        // Minimum fill time — a form submitted faster than a human can type is a bot.
        $started = (int) Session::get(self::CONTACT_STARTED_KEY, 0);
        if ($started > 0 && time() - $started < 2) {
            Session::notify('error', 'একটু ধীরে চেষ্টা করুন।');
            return $this->redirect('/contact');
        }

        $validator = Validator::make($request->body, [
            'name'    => 'required|min:2|max:80',
            'contact' => 'required|min:5|max:120',
            'message' => 'required|min:10|max:2000',
        ], ['name' => 'নাম', 'contact' => 'যোগাযোগের নম্বর বা Email', 'message' => 'বার্তা']);

        if ($validator->fails()) {
            $validator->flash();
            Session::notify('error', (string) $validator->firstError());
            return $this->redirect('/contact');
        }

        $name    = (string) $validator->get('name');
        $message = (string) $validator->get('message');
        $preview = str_excerpt($message, 120);

        $messageId = (new ContactRepo())->create($name, (string) $validator->get('contact'), $message, $request->ipHash());

        AuditService::log('contact.submitted', 'user', null, 'contact', $messageId, [
            'name'    => $name,
            'preview' => $preview,
        ], $request->ipHash());

        Notifier::contactReceived($messageId, $name, $preview);
        Session::forget(self::CONTACT_STARTED_KEY);

        Session::notify('success', 'বার্তা পেয়েছি। আমরা শীঘ্রই যোগাযোগ করব।');
        return $this->redirect('/contact');
    }

    public function sitemap(Request $request): Response
    {
        $baseUrl = (string) config('app.url');

        $words  = Db::all("SELECT slug, created_at FROM words WHERE is_exclusive = 0 ORDER BY created_at DESC LIMIT 5000");
        $guides = Db::all("SELECT slug, published_at FROM guides WHERE is_published = 1 ORDER BY published_at DESC LIMIT 1000");

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach (['/', '/dictionary', '/guides', '/subscribe'] as $path) {
            $xml .= '  <url><loc>' . e($baseUrl . $path) . '</loc></url>' . "\n";
        }
        foreach ($words as $w) {
            $xml .= '  <url><loc>' . e($baseUrl . '/dictionary/' . $w['slug']) . '</loc>'
                . '<lastmod>' . date('Y-m-d', strtotime((string) $w['created_at'])) . '</lastmod></url>' . "\n";
        }
        foreach ($guides as $g) {
            $xml .= '  <url><loc>' . e($baseUrl . '/guides/' . $g['slug']) . '</loc>'
                . '<lastmod>' . date('Y-m-d', strtotime((string) $g['published_at'])) . '</lastmod></url>' . "\n";
        }
        $xml .= '</urlset>';

        return Response::text($xml, 200, 'application/xml');
    }
}
