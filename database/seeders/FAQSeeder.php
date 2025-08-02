<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FAQSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $faqs = [
            [
                'question' => 'What is KO Talks?',
                'answer' => 'KO Talks is a speaker-driven platform by Knowledge Oman that hosts inspiring and insightful sessions from professionals across various fields.',
            ],
            [
                'question' => 'Who can attend KO Talks events?',
                'answer' => 'Anyone interested in learning, networking, and gaining insights from professionals can attend.',
            ],
            [
                'question' => 'Where are KO Talks events held?',
                'answer' => 'Events are held across various locations in Oman. Details are shared on the event page and via email.',
            ],
            [
                'question' => 'How do I register as a speaker for KO Talks?',
                'answer' => 'Visit the Speaker Registration page, complete the form, and submit your topic. Our team will contact you after review.',
            ],
            [
                'question' => 'What topics are accepted for KO Talks?',
                'answer' => 'Topics in leadership, education, entrepreneurship, innovation, technology, health, social development, and personal growth are welcome.',
            ],
            [
                'question' => 'Will I be paid to speak at KO Talks?',
                'answer' => 'KO Talks is a volunteer-based platform. Speakers share knowledge as a service to the community.',
            ],
            [
                'question' => 'How long should my talk be?',
                'answer' => 'Talks typically last 10–15 minutes, followed by a short Q&A session.',
            ],
            [
                'question' => 'Can I use a presentation or slides?',
                'answer' => 'Yes, you may use visuals. Please submit your slides at least 48 hours before the event.',
            ],
            [
                'question' => 'Will my talk be recorded or published?',
                'answer' => 'Yes, with your permission, talks may be recorded and shared on KO’s platforms.',
            ],
            [
                'question' => 'How do I register for a KO Talks event?',
                'answer' => 'Go to the Events page, choose an event, and complete the registration form.',
            ],
            [
                'question' => 'Do I need to bring a ticket to the event?',
                'answer' => 'Yes, you may present either a digital or printed ticket at the entrance.',
            ],
            [
                'question' => 'Can I bring a guest with me?',
                'answer' => 'Yes, but each guest must be registered separately.',
            ],
            [
                'question' => 'Are there virtual events or live streams?',
                'answer' => 'Some events are live-streamed or hosted online. Check event details for availability.',
            ],
            [
                'question' => 'I didn’t receive my confirmation email. What should I do?',
                'answer' => 'Check your spam folder. If it’s missing, contact support at info@kotalks.com.',
            ],
            [
                'question' => 'I’m having trouble submitting the speaker form. What can I do?',
                'answer' => 'Ensure all fields are filled and file sizes are within limits. If the issue persists, email your application to us.',
            ],
            [
                'question' => 'How can I get in touch with the KO Talks team?',
                'answer' => 'Email us at info@kotalks.com or use the contact form on our website.',
            ],
            [
                'question' => 'Can I suggest a speaker or topic?',
                'answer' => 'Yes, use the Suggestion Form on the website or email us with your idea.',
            ],
            [
                'question' => 'How do I volunteer or join the KO Talks organizing team?',
                'answer' => 'Visit the Volunteer Page and fill out the form. We’ll contact you if there’s a suitable opportunity.',
            ],
        ];
        foreach ($faqs as $faq) {
            \App\Models\FAQ::create([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'is_active' => true,
            ]);
        }
        }
}
