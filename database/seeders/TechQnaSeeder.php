<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TechQnaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::first();
        }
        if (!$admin) {
            $this->command->error('No users found. Run UserSeeder first.');
            return;
        }

        $user2 = $admin;
        $user3 = $admin;

        // Ensure Tech & IT categories exist
        $techCat = Category::firstOrCreate(['slug' => 'technology'], [
            'name_en'   => 'Technology',
            'name_ne'   => 'प्रविधि',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $itCat = Category::firstOrCreate(['slug' => 'information-technology'], [
            'name_en'   => 'Information Technology',
            'name_ne'   => 'सूचना प्रविधि',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Ensure tags exist
        $tagMap = [];
        foreach (['PHP', 'Laravel', 'JavaScript', 'Python', 'Linux', 'Networking', 'Database', 'MySQL', 'React', 'Docker', 'Git', 'API', 'Security', 'Cloud', 'IT'] as $name) {
            $tag = Tag::firstOrCreate(['slug' => Str::slug($name)], ['name_en' => $name]);
            $tagMap[Str::slug($name)] = $tag->id;
        }

        $questions = [
            [
                'user_id'     => $admin->id,
                'category_id' => $techCat->id,
                'title'       => 'What is the difference between Laravel Sanctum and Passport for API authentication?',
                'content'     => "I'm building a REST API with Laravel and I'm confused about when to use Sanctum vs Passport.\n\nMy use cases:\n- Mobile app authentication\n- SPA (Vue.js) authentication\n- Third-party API access\n\nWhich one should I choose and why? What are the trade-offs?",
                'tags'        => ['laravel', 'api', 'php'],
                'votes'       => 12,
                'answers'     => [
                    [
                        'user_id' => $user2->id,
                        'content' => "**Sanctum** is the simpler choice for most cases:\n\n- **SPA auth**: Uses cookie-based sessions — perfect for Vue/React SPAs on the same domain\n- **Mobile/API tokens**: Issues simple bearer tokens stored in your DB\n- Zero OAuth overhead\n\n**Passport** is for when you need full OAuth2:\n- Third-party apps need to authenticate via your server\n- You want `authorization_code`, `client_credentials`, or `password` grant flows\n- You're building an OAuth provider yourself\n\n**Rule of thumb**: If you control all the clients (your own SPA + mobile app), use Sanctum. If you need to let external developers build integrations, use Passport.",
                        'votes'   => 8,
                        'is_best' => true,
                    ],
                    [
                        'user_id' => $user3->id,
                        'content' => "I switched from Passport to Sanctum last year for our SPA + mobile API and it was much simpler to set up. Sanctum tokens are just hashed strings in the `personal_access_tokens` table — easy to manage and revoke.\n\nPassport adds JWT tokens, multiple grant types, and requires more config. Unless you need OAuth2 flows for third parties, Sanctum is the way to go.",
                        'votes'   => 5,
                        'is_best' => false,
                    ],
                ],
            ],
            [
                'user_id'     => $user2->id,
                'category_id' => $itCat->id,
                'title'       => 'How do I set up SSH key-based authentication on a Linux server?',
                'content'     => "I want to disable password login on my Ubuntu server and use SSH keys instead for better security. Can someone walk me through the process?\n\nI'm running Ubuntu 22.04 LTS.",
                'tags'        => ['linux', 'security', 'networking'],
                'votes'       => 18,
                'answers'     => [
                    [
                        'user_id' => $admin->id,
                        'content' => "Here's the step-by-step process:\n\n**1. Generate a key pair on your local machine:**\n```\nssh-keygen -t ed25519 -C \"your_email@example.com\"\n```\n\n**2. Copy the public key to the server:**\n```\nssh-copy-id username@your_server_ip\n```\n\n**3. Disable password auth** — edit `/etc/ssh/sshd_config`:\n```\nPasswordAuthentication no\nPubkeyAuthentication yes\n```\n\n**4. Restart SSH:**\n```\nsudo systemctl restart sshd\n```\n\n⚠️ Keep your current session open and test in a new terminal first before closing — if the key doesn't work and you disabled passwords, you'll be locked out.",
                        'votes'   => 14,
                        'is_best' => true,
                    ],
                    [
                        'user_id' => $user3->id,
                        'content' => "Adding to the above — also consider:\n\n- Change the default SSH port (22) to something non-standard to reduce brute force attempts\n- Install `fail2ban` to auto-ban IPs with too many failed attempts\n- Use `AllowUsers username` in sshd_config to restrict which users can SSH in\n\nThese extra steps significantly harden your server.",
                        'votes'   => 6,
                        'is_best' => false,
                    ],
                ],
            ],
            [
                'user_id'     => $user3->id,
                'category_id' => $techCat->id,
                'title'       => 'MySQL vs PostgreSQL: which should I choose for a new Laravel project?',
                'content'     => "Starting a new Laravel project and can't decide between MySQL and PostgreSQL. The app will handle:\n- About 500k records in the main table\n- Complex reporting queries with joins\n- JSON data storage for flexible fields\n- Full-text search\n\nWhat are the real-world differences I should care about?",
                'tags'        => ['mysql', 'database', 'laravel'],
                'votes'       => 9,
                'answers'     => [
                    [
                        'user_id' => $user2->id,
                        'content' => "Both work great with Laravel, but here are the practical differences:\n\n**Choose PostgreSQL if:**\n- You need advanced JSON queries (`jsonb` is far superior to MySQL's JSON)\n- Complex reporting with window functions, CTEs, lateral joins\n- Full-text search (PostgreSQL's built-in FTS beats MySQL's)\n- Strict data integrity matters (better constraint support)\n\n**Choose MySQL if:**\n- Your team/host knows MySQL better\n- You're using shared hosting (MySQL is more widely available)\n- Simple CRUD app with standard queries\n- You need Vitess or PlanetScale scaling\n\nFor your use case with complex reporting and JSON — **PostgreSQL** is the better fit.",
                        'votes'   => 7,
                        'is_best' => true,
                    ],
                ],
            ],
            [
                'user_id'     => $admin->id,
                'category_id' => $itCat->id,
                'title'       => 'What is Docker and how is it different from a Virtual Machine?',
                'content'     => "I keep hearing about Docker at work but I'm not fully clear on what it actually does. How is it different from just spinning up a VM? When should I use one vs the other?",
                'tags'        => ['docker', 'linux', 'cloud'],
                'votes'       => 22,
                'answers'     => [
                    [
                        'user_id' => $user3->id,
                        'content' => "Great question! The key difference is **what they virtualize**:\n\n**Virtual Machine** virtualizes the entire hardware — it runs a full OS with its own kernel on top of a hypervisor. Heavy, boots in minutes, complete isolation.\n\n**Docker container** virtualizes just the application layer — it shares the host OS kernel but packages the app + its dependencies into an isolated unit. Lightweight, starts in seconds, lower overhead.\n\n**Analogy**: A VM is like a full apartment (with its own plumbing, electricity). A container is like a room in a shared house — it has its own space but shares infrastructure.\n\n**Use Docker when**: deploying apps consistently across dev/staging/prod, microservices, CI/CD pipelines.\n**Use VMs when**: you need full OS isolation, running different OS kernels, or legacy app compatibility.",
                        'votes'   => 15,
                        'is_best' => true,
                    ],
                    [
                        'user_id' => $user2->id,
                        'content' => "To add a practical perspective — in modern web development Docker is almost always the better choice for application deployment. A typical setup:\n\n- `docker-compose.yml` defines your app, DB, Redis, etc.\n- `docker build` creates a reproducible image\n- Same image runs on your laptop, CI server, and production\n\nThis eliminates the classic \"works on my machine\" problem. VMs are more useful at the infrastructure level (cloud providers use them under the hood to give you isolated compute).",
                        'votes'   => 9,
                        'is_best' => false,
                    ],
                ],
            ],
            [
                'user_id'     => $user2->id,
                'category_id' => $techCat->id,
                'title'       => 'How do I optimize slow database queries in Laravel?',
                'content'     => "My Laravel app is getting slow as data grows. Some pages take 3-5 seconds to load. I suspect it's the database queries.\n\nWhat are the best tools and techniques to identify and fix slow queries in Laravel?",
                'tags'        => ['laravel', 'mysql', 'database', 'php'],
                'votes'       => 15,
                'answers'     => [
                    [
                        'user_id' => $admin->id,
                        'content' => "Here's a systematic approach:\n\n**1. Find the slow queries first**\nInstall Laravel Debugbar (`barryvdh/laravel-debugbar`) or use `DB::listen()` to log queries:\n```php\nDB::listen(function($query) {\n    if ($query->time > 100) { // log queries over 100ms\n        Log::warning($query->sql, $query->bindings);\n    }\n});\n```\n\n**2. Fix N+1 queries** — most common culprit:\n```php\n// Bad:\n$posts = Post::all();\nforeach ($posts as $post) {\n    echo $post->user->name; // N queries!\n}\n\n// Good:\n$posts = Post::with('user')->get(); // 2 queries total\n```\n\n**3. Add database indexes** on columns you filter/sort by:\n```sql\nCREATE INDEX idx_posts_status_created ON posts(status, created_at);\n```\n\n**4. Use `EXPLAIN`** on slow queries to see if indexes are being used.\n\n**5. Cache expensive queries** with `Cache::remember()`.",
                        'votes'   => 11,
                        'is_best' => true,
                    ],
                ],
            ],
            [
                'user_id'     => $user3->id,
                'category_id' => $itCat->id,
                'title'       => 'What is the difference between Git merge and Git rebase?',
                'content'     => "I use Git daily but I've never fully understood when to use `merge` vs `rebase`. My team sometimes argues about it. Can someone explain the practical difference and when to use each?",
                'tags'        => ['git'],
                'votes'       => 20,
                'answers'     => [
                    [
                        'user_id' => $user2->id,
                        'content' => "Both integrate changes from one branch into another, but they do it differently:\n\n**Merge** creates a new \"merge commit\" that ties together both branch histories. History is preserved exactly as it happened — you can see when branches diverged and joined.\n\n**Rebase** rewrites history by replaying your commits on top of another branch. The result looks like you always worked on a straight line — cleaner history, but the commit hashes change.\n\n**When to use each:**\n- `merge` for merging feature branches into main (preserves context)\n- `rebase` to update your feature branch with latest main before opening a PR (cleaner diff)\n- **Never rebase** shared/public branches — it rewrites history that others depend on\n\n**Golden rule**: rebase locally, merge publicly.",
                        'votes'   => 13,
                        'is_best' => true,
                    ],
                    [
                        'user_id' => $admin->id,
                        'content' => "A practical workflow many teams use:\n\n1. Work on feature branch\n2. `git fetch origin && git rebase origin/main` to update your branch (keeps history clean)\n3. Open PR — the PR shows only your changes, not a tangle of merge commits\n4. Reviewer merges the PR (merge commit in main marks when the feature landed)\n\nThis gives you clean local history + clear merge points in main.",
                        'votes'   => 8,
                        'is_best' => false,
                    ],
                ],
            ],
            [
                'user_id'     => $admin->id,
                'category_id' => $techCat->id,
                'title'       => 'How does React useState hook work under the hood?',
                'content'     => "I use React hooks every day but I want to understand what's actually happening when I call `useState`. How does React know which state belongs to which component when there's no class instance?",
                'tags'        => ['javascript', 'react'],
                'votes'       => 11,
                'answers'     => [
                    [
                        'user_id' => $user3->id,
                        'content' => "React maintains a **hooks state array** per component fiber (its internal tree node). Each `useState` call corresponds to an index in that array, determined by the **call order**.\n\n```js\n// When your component renders:\nconst [count, setCount] = useState(0);  // index 0\nconst [name, setName]   = useState(''); // index 1\n```\n\nOn first render, React initializes slot 0 = 0, slot 1 = ''. On re-render, it reads the current values at those same slots.\n\nThis is **why hooks must be called unconditionally** — if you wrap a hook in an `if`, the call order changes between renders and React reads the wrong slot.\n\nCalling `setCount(5)` schedules a re-render and updates slot 0 to 5. React diffs the old and new renders and updates the DOM only where needed.",
                        'votes'   => 9,
                        'is_best' => true,
                    ],
                ],
            ],
            [
                'user_id'     => $user2->id,
                'category_id' => $itCat->id,
                'title'       => 'What is CORS and how do I fix a CORS error in my API?',
                'content'     => "I'm getting this error in my browser console when my React frontend calls my Laravel API:\n\n```\nAccess to XMLHttpRequest at 'http://api.myapp.com/users' from origin 'http://app.myapp.com' has been blocked by CORS policy\n```\n\nWhat is CORS and how do I fix this?",
                'tags'        => ['api', 'laravel', 'javascript', 'security'],
                'votes'       => 25,
                'answers'     => [
                    [
                        'user_id' => $admin->id,
                        'content' => "**CORS (Cross-Origin Resource Sharing)** is a browser security mechanism. By default, browsers block JavaScript from making requests to a different origin (domain/port/protocol) than the page it's on.\n\nYour API must tell the browser \"this origin is allowed\" via response headers.\n\n**Fix in Laravel** — in `config/cors.php`:\n```php\n'allowed_origins' => ['http://app.myapp.com'],\n// or during development:\n'allowed_origins' => ['*'],\n```\n\nMake sure `\\Fruitcake\\Laravel\\Cors\\HandleCors::class` (or `\\Illuminate\\Http\\Middleware\\HandleCors::class` in Laravel 11) is in your middleware.\n\n**For local dev**, you can also proxy API calls through Vite/webpack dev server so they appear same-origin — avoids CORS entirely in development.",
                        'votes'   => 18,
                        'is_best' => true,
                    ],
                    [
                        'user_id' => $user3->id,
                        'content' => "Important note: CORS is **browser-only**. Tools like curl, Postman, or server-to-server calls are never affected — only browser JavaScript.\n\nAlso watch out for **preflight requests** — browsers send an `OPTIONS` request before `POST`/`PUT`/`DELETE` with custom headers. Make sure your server handles `OPTIONS` and returns the CORS headers for those too (Laravel's CORS middleware handles this automatically).",
                        'votes'   => 10,
                        'is_best' => false,
                    ],
                ],
            ],
        ];

        foreach ($questions as $qData) {
            $slug = Str::slug($qData['title']);
            $base = $slug; $i = 1;
            while (Question::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}"; $i++;
            }

            $question = Question::create([
                'user_id'       => $qData['user_id'],
                'category_id'   => $qData['category_id'],
                'title'         => $qData['title'],
                'slug'          => $slug,
                'content'       => $qData['content'],
                'status'        => 'open',
                'votes'         => $qData['votes'],
                'views_count'   => rand(50, 800),
                'answers_count' => count($qData['answers']),
                'notify_email'  => true,
            ]);

            // Attach tags
            $tagIds = [];
            foreach ($qData['tags'] as $tagSlug) {
                if (isset($tagMap[$tagSlug])) {
                    $tagIds[] = $tagMap[$tagSlug];
                }
            }
            if ($tagIds) $question->tags()->sync($tagIds);

            // Create answers
            $bestAnswerId = null;
            foreach ($qData['answers'] as $aData) {
                $answer = Answer::create([
                    'question_id'  => $question->id,
                    'user_id'      => $aData['user_id'],
                    'content'      => $aData['content'],
                    'votes'        => $aData['votes'],
                    'is_best'      => $aData['is_best'],
                    'is_anonymous' => false,
                ]);
                if ($aData['is_best']) {
                    $bestAnswerId = $answer->id;
                }
            }

            if ($bestAnswerId) {
                $question->update(['best_answer_id' => $bestAnswerId]);
            }
        }

        $this->command->info('Tech/IT Q&A seed complete — ' . count($questions) . ' questions added.');
    }
}
