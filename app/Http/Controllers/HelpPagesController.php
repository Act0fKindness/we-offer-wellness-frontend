<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class HelpPagesController extends Controller
{
    public function faq()
    {
        $body = <<<'HTML'
<h2>Frequently asked questions</h2>
<p><strong>How do I manage a booking?</strong><br>Visit your confirmation email to reschedule or cancel, or message the practitioner directly from your account.</p>
<p><strong>What if I need to cancel?</strong><br>Each listing includes a cancellation window. If you cannot find it, <a href="/contact?topic=support">contact support</a>.</p>
<p><strong>Do I need any equipment?</strong><br>Most therapies only require comfortable clothing and a quiet space. Classes will note props if needed.</p>
HTML;
        return Inertia::render('General/Page', [
            'title' => 'FAQ',
            'metaDescription' => 'Common booking, payment and account questions.',
            'bodyHtml' => $body,
            'canonical' => url('/help/faq'),
        ]);
    }

    public function giftCards()
    {
        $body = <<<'HTML'
<p>WOW gift cards arrive instantly by email with your personalised message. Gift cards never expire and can be redeemed on therapies, classes, events and workshops.</p>
<ul>
  <li><strong>How to send:</strong> Choose an amount, enter the recipient’s name and email, and schedule delivery or send immediately.</li>
  <li><strong>How to redeem:</strong> Recipients enter the unique code at checkout. Balances can be used across multiple bookings.</li>
  <li><strong>Need a custom amount?</strong> <a href="/contact?topic=gifting">Contact the team</a> for bulk or corporate gifting.</li>
  <li><a href="/gift-cards">Browse gift cards</a></li>
  <li><a href="/gifts">See gifting ideas</a></li>
  <li><a href="/refunds-and-cancellations">Refunds & cancellations</a></li>
  <li><a href="/privacy">Privacy policy</a></li>
  <li><a href="/terms">Terms & conditions</a></li>
  <li><a href="/cookies">Cookies</a></li>
</ul>
HTML;
        return Inertia::render('General/Page', [
            'title' => 'Gift Card Help',
            'metaDescription' => 'How WOW gift vouchers work and how to redeem them.',
            'bodyHtml' => $body,
            'canonical' => url('/help/gift-cards'),
        ]);
    }
}

