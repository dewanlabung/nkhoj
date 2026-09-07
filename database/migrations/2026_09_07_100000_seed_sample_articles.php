<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if sample articles already exist
        if (DB::table('posts')->where('slug', 'like', 'sample-%')->exists()) {
            return;
        }

        $adminId = DB::table('users')->where('role', 'admin')->value('id')
            ?? DB::table('users')->orderBy('id')->value('id');

        $categoryId = DB::table('categories')->orderBy('sort_order')->value('id')
            ?? DB::table('categories')->orderBy('id')->value('id');

        if (!$adminId) {
            return; // No users yet, skip
        }

        $articles = [
            [
                'slug'         => 'sample-how-nepal-is-embracing-digital-transformation',
                'title'        => 'How Nepal Is Embracing Digital Transformation in 2025',
                'excerpt'      => 'From mobile banking to e-governance, Nepal is rapidly modernizing its digital infrastructure. Here's a look at the key trends shaping the country's tech future.',
                'thumbnail'    => 'https://picsum.photos/seed/nepal-tech/1200/630',
                'body'         => [
                    ['type' => 'paragraph', 'content' => 'Nepal has seen remarkable growth in digital adoption over the past few years. With smartphone penetration exceeding 70% and mobile internet becoming the primary means of connectivity, the country is undergoing a quiet but significant digital revolution.'],
                    ['type' => 'paragraph', 'content' => 'The government's push for e-governance has resulted in platforms like the Nagarik App, which lets citizens access dozens of public services from their phones. Tax filings, land records, and birth certificates can now be obtained digitally, cutting red tape that once took days or weeks.'],
                    ['type' => 'paragraph', 'content' => 'Fintech has been another major driver. Khalti and eSewa now process billions of rupees monthly, and even small tea shops in remote districts accept QR-based payments. The Nepal Rastra Bank's regulatory sandbox has encouraged innovation while maintaining financial stability.'],
                    ['type' => 'paragraph', 'content' => 'The startup ecosystem in Kathmandu is maturing, with co-working spaces, accelerators, and a growing community of developers. While challenges remain — including inconsistent power supply and high bandwidth costs — the trajectory is clearly upward.'],
                    ['type' => 'paragraph', 'content' => 'Experts believe that with sustained investment in digital infrastructure and education, Nepal could position itself as a regional technology hub within the next decade.'],
                ],
                'faq'          => [
                    ['q' => 'What is the Nagarik App?', 'a' => 'The Nagarik App is a government-run mobile platform that lets Nepali citizens access various public services such as tax filings, land records, and document applications digitally.'],
                    ['q' => 'Which digital wallets are most popular in Nepal?', 'a' => 'eSewa and Khalti are the two most widely used digital wallets in Nepal, together processing billions of rupees in transactions every month.'],
                    ['q' => 'Is Nepal's startup ecosystem growing?', 'a' => 'Yes — Kathmandu has seen a rise in co-working spaces, tech accelerators, and developer communities, indicating a maturing startup culture, though challenges like infrastructure gaps remain.'],
                ],
            ],
            [
                'slug'         => 'sample-10-tips-for-productive-remote-work',
                'title'        => '10 Proven Tips for Staying Productive While Working Remotely',
                'excerpt'      => 'Remote work is here to stay. Whether you're a freelancer or part of a distributed team, these practical strategies will help you stay focused and deliver your best work every day.',
                'thumbnail'    => 'https://picsum.photos/seed/remote-work/1200/630',
                'body'         => [
                    ['type' => 'paragraph', 'content' => 'Remote work offers incredible flexibility, but without structure it can quickly become chaotic. The most productive remote workers share a set of habits that keep them on track regardless of distractions.'],
                    ['type' => 'paragraph', 'content' => '1. Create a dedicated workspace. Even in a small apartment, carving out a specific spot for work trains your brain to switch into "work mode" the moment you sit down.'],
                    ['type' => 'paragraph', 'content' => '2. Stick to consistent hours. Start and end at the same time each day. This creates boundaries and prevents work from bleeding into personal time.'],
                    ['type' => 'paragraph', 'content' => '3. Use the Pomodoro technique. Work in 25-minute focused sprints with 5-minute breaks. After four rounds, take a longer 20-minute break to recharge.'],
                    ['type' => 'paragraph', 'content' => '4. Dress for work. It sounds trivial, but changing out of pajamas signals to your mind that it's time to be professional and productive.'],
                    ['type' => 'paragraph', 'content' => '5. Minimize digital distractions. Use tools like Focus Mode or website blockers during deep work sessions. Notifications are productivity killers.'],
                    ['type' => 'paragraph', 'content' => '6. Communicate proactively. Since colleagues can't see you working, over-communicate your progress, blockers, and availability to stay aligned.'],
                    ['type' => 'paragraph', 'content' => '7. Take real breaks. Step outside, do some stretches, or make a coffee. Breaks are not wasted time — they restore focus and reduce burnout.'],
                    ['type' => 'paragraph', 'content' => '8. End with a shutdown ritual. Write tomorrow's top three priorities, then close your laptop. This mental "close" helps separate work from rest.'],
                    ['type' => 'paragraph', 'content' => '9. Invest in good equipment. A reliable internet connection, ergonomic chair, and quality headphones pay dividends in comfort and productivity.'],
                    ['type' => 'paragraph', 'content' => '10. Stay socially connected. Schedule virtual coffee chats or use team channels for casual conversation. Remote work can be isolating — intentional connection matters.'],
                ],
                'faq'          => [
                    ['q' => 'How do I stay motivated when working from home?', 'a' => 'Set clear daily goals, maintain a routine, and create a dedicated workspace. Social accountability — like sharing your goals with a colleague — also helps significantly.'],
                    ['q' => 'What is the Pomodoro technique?', 'a' => 'The Pomodoro technique involves working in 25-minute focused intervals (called "pomodoros") separated by 5-minute breaks, with a longer break after every four intervals.'],
                    ['q' => 'Is remote work suitable for all job types?', 'a' => 'Not all roles are suited to remote work, but knowledge work, creative roles, and software development are particularly well-adapted. The key is having clear communication and measurable outcomes.'],
                ],
            ],
            [
                'slug'         => 'sample-beginners-guide-to-personal-finance',
                'title'        => 'A Beginner's Guide to Personal Finance: Build Wealth Step by Step',
                'excerpt'      => 'Managing your money doesn't have to be complicated. This beginner-friendly guide covers budgeting, saving, investing, and building an emergency fund — the foundations of lasting financial health.',
                'thumbnail'    => 'https://picsum.photos/seed/personal-finance/1200/630',
                'body'         => [
                    ['type' => 'paragraph', 'content' => 'Personal finance is one of the most important life skills — yet it's rarely taught in school. The good news is that the core principles are simple and actionable, regardless of your income level.'],
                    ['type' => 'paragraph', 'content' => 'Start with a budget. The 50/30/20 rule is a popular framework: spend 50% of your after-tax income on needs (rent, groceries, utilities), 30% on wants (dining out, entertainment), and save or invest 20%.'],
                    ['type' => 'paragraph', 'content' => 'Build an emergency fund before anything else. Aim for three to six months of living expenses in a liquid savings account. This cushion protects you from financial shocks like job loss or a medical emergency.'],
                    ['type' => 'paragraph', 'content' => 'Eliminate high-interest debt as quickly as possible. Credit card interest rates of 15–25% are a guaranteed return if you pay them off. Focus extra money on the highest-rate debt first (the "avalanche" method).'],
                    ['type' => 'paragraph', 'content' => 'Once debt is under control, start investing early. Even small amounts compound dramatically over time. Index funds or low-cost mutual funds are excellent starting points for beginners.'],
                    ['type' => 'paragraph', 'content' => 'Track your net worth monthly — assets minus liabilities. Watching this number grow (even slowly) is one of the most motivating things you can do for your financial discipline.'],
                    ['type' => 'paragraph', 'content' => 'Finally, automate everything you can. Automatic transfers to savings and investment accounts remove willpower from the equation and make good financial behavior effortless.'],
                ],
                'faq'          => [
                    ['q' => 'What is the 50/30/20 budgeting rule?', 'a' => 'The 50/30/20 rule divides your after-tax income into three categories: 50% for essential needs, 30% for personal wants, and 20% for savings and debt repayment.'],
                    ['q' => 'How much should I have in an emergency fund?', 'a' => 'Financial advisors generally recommend saving three to six months of living expenses in an easily accessible, liquid savings account.'],
                    ['q' => 'When should I start investing?', 'a' => 'As early as possible — even small amounts invested consistently benefit enormously from compound growth over time. Most experts suggest starting once you have an emergency fund and no high-interest debt.'],
                ],
            ],
        ];

        $now = now();

        foreach ($articles as $article) {
            $postId = DB::table('posts')->insertGetId([
                'uuid'          => (string) Str::uuid(),
                'author_id'     => $adminId,
                'category_id'   => $categoryId,
                'slug'          => $article['slug'],
                'title'         => $article['title'],
                'excerpt'       => $article['excerpt'],
                'body'          => json_encode($article['body']),
                'article_faq'   => json_encode($article['faq']),
                'thumbnail_url' => $article['thumbnail'],
                'status'        => 'published',
                'post_format'   => 'article',
                'is_featured'   => false,
                'is_pro'        => false,
                'view_count'    => 0,
                'published_at'  => $now,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('posts')->where('slug', 'like', 'sample-%')->delete();
    }
};
