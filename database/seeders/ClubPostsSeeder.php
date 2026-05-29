<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\PostMedia;
use App\Models\Publication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClubPostsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Resolve club groups by slug ───────────────────────────────────────
        $groups = Group::whereIn('slug', [
            'club-bridge', 'club-cindh', 'club-ensias-founders', 'club-fintech',
            'club-forum-genie', 'club-house-of-art', 'club-japonais',
            'club-neurodynamics', 'club-quraan', 'club-sportif',
            'eitc', 'insec',
        ])->get()->keyBy('slug');

        $defaultUser = User::first();
        if (! $defaultUser) {
            $this->command->warn('No users found – aborting ClubPostsSeeder.');
            return;
        }

        // Helper: resolve group or warn + skip
        $group = function (string $slug) use ($groups): ?Group {
            $g = $groups->get($slug);
            if (! $g) {
                $this->command->warn("Group not found for slug [{$slug}] – post skipped.");
            }
            return $g;
        };

        // Helper: build image URLs and skip missing files
        $imgs = function (string $club, array $files): array {
            $urls = [];
            foreach ($files as $file) {
                $path = storage_path("app/public/clubs/{$club}/{$file}");
                if (file_exists($path)) {
                    $urls[] = "/storage/clubs/{$club}/{$file}";
                } else {
                    $this->command->warn("Image not found: clubs/{$club}/{$file} – using placeholder.");
                    $urls[] = "/storage/placeholder.jpg";
                }
            }
            return $urls;
        };

        // Helper: create a publication + its media rows
        $create = function (
            ?Group $grp,
            string $contenu,
            array  $imageUrls,
            Carbon $date
        ) use ($defaultUser): void {
            if (! $grp) return;

            $pub = Publication::create([
                'id'               => (string) Str::uuid(),
                'contenu'          => $contenu,
                'typeMedia'        => count($imageUrls) ? 'image' : null,
                'statutValidation' => 'Valide',
                'visibility'       => 'group',
                'user_id'          => $defaultUser->id,
                'groupe_id'        => $grp->id,
                'publishedAt'      => $date,
                'created_at'       => $date,
                'updated_at'       => $date,
            ]);

            foreach ($imageUrls as $order => $url) {
                PostMedia::create([
                    'id'             => (string) Str::uuid(),
                    'publication_id' => $pub->id,
                    'url'            => $url,
                    'type'           => 'image',
                    'thumbnail_url'  => null,
                    'order'          => $order,
                ]);
            }
        };

        // ── Club Bridge ───────────────────────────────────────────────────────
        $g = $group('club-bridge');

        $create($g,
            "New leaders, same spirit ✨🧡\nAfter much anticipation, it's time to meet the 15th pilotage team of Bridge Club 🌉— carrying forward a legacy of excellence, connection, and ambition. A big thanks to the previous team — you paved the way.",
            $imgs('bridge', ['post1_img1.jpg', 'post1_img2.jpg', 'post1_img3.jpg']),
            Carbon::now()->subDays(55)
        );

        $create($g,
            "From inspiration to innovation 💡✨\n\"Bridge to IT\" was more than just an event, it was a space to learn, connect, and grow together.\nHuge thanks to everyone who joined us for the Orientation & Skills Workshop Series, proudly organized by ENSIAS IT Club × ENSIAS Bridge💙🧡. #EITC #Bridge #SkillsWorkshop #OrientationSeries",
            $imgs('bridge', ['post2_img1.jpg', 'post2_img2.jpg', 'post2_img3.jpg']),
            Carbon::now()->subDays(12)
        );

        $create($g,
            "Tonight's workshop is all about Data Engineering & Apache Spark with a hands-on session led by Rida Massoui, Data Engineer & Databricks Certified @ Amadeus.\n\n📅 19/05 | 🕖 19:00 | 📍 Salle Rouge – ENSIAS\n\nJoin us for an interactive session around Big Data, Spark fundamentals & real-world Data Engineering workflows ⚡💻 #BridgeENSIAS #DataEngineering",
            $imgs('bridge', ['post3_img1.jpg']),
            Carbon::parse('2026-05-19 18:00:00')
        );

        $create($g,
            "Tonight we kick off the first workshop of our event series with an exciting session on Software Engineering & Agentic AI led by El Mehdi Assali, Senior Software Engineer @ BCG X.\n\n📅 18/05 | 🕖 19:00 | 📍 Salle Rouge – ENSIAS\n\nLet's build, learn, and connect 💡🔥 #BridgeENSIAS #SoftwareEngineering #aiagent #GenAI",
            $imgs('bridge', ['post4_img1.jpg']),
            Carbon::parse('2026-05-18 18:00:00')
        );

        $create($g,
            "Big collab alert 🤝\nIT × Bridge ENSIAS are joining forces to bring you the ultimate IT orientation experience!\n\n🗓 May 18–20 | 3 Days – 3 Tracks\n• Software Engineering | • Data Engineering | • Project Studio\n⏰ Briefing: 19:00 | 🛠 Workshop: 20:15 | 📍 Salle Rouge\n⚠️ Limited places available!",
            $imgs('bridge', ['post5_img1.jpg', 'post5_img2.jpg']),
            Carbon::parse('2026-05-17 20:00:00')
        );

        // ── Club CINDH ────────────────────────────────────────────────────────
        $g = $group('club-cindh');

        $create($g,
            "في ليلةٍ من ليالي العيد، جلس طفلٌ صغير ينتظر نصيبه من الفرح، يراقب الأضواء والناس من بعيد، ويتساءل في براءة: هل سيزورنا العيد هذا العام؟ بمساهمتكم، يمكن أن تصل الأضاحي والكسوة والقفف الغذائية إلى بيوتٍ تنتظر. فالعيد يكتمل حين نتقاسمه. 💙💛🐑",
            $imgs('cindh', ['post1_img1.jpg']),
            Carbon::now()->subDays(40)
        );

        $create($g,
            "مع اقتراب أيام عيد الأضحى المبارك، يسرّ نادينا الخيري \"CINDH\" أن يتقدم لكم بأطيب التهاني وأصدق التمنيات. نسأل الله العلي القدير أن يرزقكم من وافر فضله، وأن يحفظكم بعينه التي لا تنام.",
            $imgs('cindh', ['post2_img1.jpg']),
            Carbon::now()->subDays(38)
        );

        $create($g,
            "عن عبد الله بن عمر رضي الله عنهما، أن رسول الله ﷺ قال: «أحبُّ الناسِ إلى اللهِ تَعالَى أنفَعُهم للناسِ»\nتستمر حملة فرحة العيد في نادينا بفتح أبواب الخير واستقبال عطاءاتكم لتوفيرِ الأضاحي للأسرِ الأشدِّ فاقة وحاجة.",
            $imgs('cindh', ['post3_img1.jpg']),
            Carbon::now()->subDays(36)
        );

        $create($g,
            "أيامٌ الأجرُ فيها مضاعف... بفضل الله وبفضل تبرعاتكم، تمكّنا ولله الحمد من جمع ثمن ثلاث عشرة أضحية، ولا يزال الخيرُ مستمرًّا باستمرار تبرعاتكم، رزقكم الله بقدر ما تصدّقتم أضعافًا.",
            $imgs('cindh', ['post4_img1.jpg']),
            Carbon::now()->subDays(34)
        );

        $create($g,
            "كل بذرة خير يغرسها النادي توضع بعناية... يعير نادينا مكانة خاصة للمشاريع المستدامة. تم تجهيز بئر تمامة لضمان اشتغاله، إضافة إلى العمل على بناء كتاب بدوار إكيس لدعم تربية وتعليم الأطفال.",
            $imgs('cindh', ['post5_img1.jpg', 'post5_img2.jpg', 'post5_img3.jpg',
                            'post5_img4.jpg', 'post5_img5.jpg', 'post5_img6.jpg',
                            'post5_img7.jpg', 'post5_img8.jpg', 'post5_img9.jpg',
                            'post5_img10.jpg', 'post5_img11.jpg', 'post5_img12.jpg',
                            'post5_img13.jpg', 'post5_img14.jpg', 'post5_img15.jpg',
                            'post5_img16.jpg']),
            Carbon::now()->subDays(30)
        );

        // ── Club EITC ─────────────────────────────────────────────────────────
        $g = $group('eitc');

        $create($g,
            "Training – Workshop Alert! ENSIAS IT Club invites you to a hands-on training workshop on Setting up a Web Service implementing Link Forwarding 💻🔗\n👨‍🏫 Guest Speaker: Prof. Hellman — PhD, IMT & Former Senior Software Engineer at Microsoft\n📅 Date: 12/03 - 22:30",
            $imgs('eitc', ['post1_img1.jpg']),
            Carbon::parse('2026-03-12 22:30:00')
        );

        $create($g,
            "Want to get started in web development? 👨‍💻✨\nJoin us for an interactive training workshop with Badreddine Aiz, Software Developer 🚀\n📅 Date: 12/02/2025 | ⏰ 7:30 PM | 📍 Salle A5\n#ENSIAS #ENSIAS_IT_Club #WebDev",
            $imgs('eitc', ['post2_img1.jpg']),
            Carbon::parse('2025-02-12 19:30:00')
        );

        $create($g,
            "Join us this Friday at 7:00 PM for the announcement of our hackathon theme! 🖥️💡\nWe will also open the registration form. Don't miss out!\n#Hackathon #Innovation #ENSIAS",
            $imgs('eitc', ['post3_img1.jpg']),
            Carbon::now()->subDays(20)
        );

        $create($g,
            "Step into the universe of clubs at ENSIAS! This Monday at 20:00, The Seven will guide you through the galaxy of opportunities with EITC at the center.\n#TheSeven #ENSIAS #EITC #Together_we_grow_in_tech",
            $imgs('eitc', ['post4_img1.jpg']),
            Carbon::now()->subDays(50)
        );

        $create($g,
            "🎮 IT ESCAPE Game Competition: The Winners Are Here! 🚀\nHuge congrats to the champions of IT ESCAPE! 🏆🎮 Your skills, strategy, and teamwork earned you the top spot.\nThanks to our amazing sponsors: RIBATIS and Cnexia 💚 #ITSCAPE #ENSIAS",
            $imgs('eitc', ['post5_img1.jpg']),
            Carbon::now()->subDays(60)
        );

        // ── Club ENSIAS Founders ──────────────────────────────────────────────
        $g = $group('club-ensias-founders');

        $create($g,
            "Workshop — Design Thinking\nDans le cadre de l'Entrepreneurship Day, nous aurons le plaisir d'accueillir Pr. Soukaina El Boujnouni pour un workshop autour du Design Thinking.\n📍 ENSIAS, Grand Amphi | 📅 13 May 2026 | 🕒 15:30\nPlaces limitées — inscription obligatoire.",
            $imgs('founders', ['post1_img1.jpg']),
            Carbon::parse('2026-05-13 12:00:00')
        );

        $create($g,
            "🚀 Entrepreneurship Day — Ambition to Action\nL'ENSIAS Founders Club vous donne rendez-vous pour une après-midi dédiée à l'entrepreneuriat, à l'innovation et au passage à l'action.\nSous le thème « Ambition to Action » — Comment passer d'une idée à un projet concret, crédible et exécutable au Maroc ?",
            $imgs('founders', ['post2_img1.jpg']),
            Carbon::parse('2026-05-10 10:00:00')
        );

        $create($g,
            "WHO ARE WE? We are not just a standard club. We're building a complete startup ecosystem inside ENSIAS.\nENSIAS Founders Club (EFC) is the hub for students who don't want their projects to die in a GitHub repo.\nWe Inspire. We Train. We Launch.\nTurn your ideas into impact.",
            $imgs('founders', ['post3_img1.jpg', 'post3_img2.jpg', 'post3_img3.jpg']),
            Carbon::now()->subDays(70)
        );

        $create($g,
            "🚀 A new era begins at ENSIAS.\nWe're proud to announce the official launch of the ENSIAS Founders Club — the first club at ENSIAS entirely dedicated to startups and innovation.\nCome to our presentation tonight at 22:30 in GA 💛",
            $imgs('founders', ['post4_img1.jpg']),
            Carbon::now()->subDays(90)
        );

        $create($g,
            "Founders Talk #1 — Nous aurons l'honneur de recevoir M. Youssef Rkaissi, entrepreneur, CEO et fondateur de RKICY Technology (lauréat ENSIAS).\n📍 ENSIAS — Grand Amphi | 📅 Jeudi 25 décembre 2025 | 🕡 Accueil : 18h30\n✅ Inscriptions ouvertes — Places limitées",
            $imgs('founders', ['post5_img1.jpg']),
            Carbon::parse('2025-12-25 18:00:00')
        );

        // ── Club Fintech ──────────────────────────────────────────────────────
        $g = $group('club-fintech');

        $create($g,
            "FinTech Family تهنئكم بحلول عيد الأضحى المبارك، أعاده الله عليكم بالخير والبركات 🤍🌙🐑",
            $imgs('fintech', ['post1_img1.jpg']),
            Carbon::now()->subDays(35)
        );

        $create($g,
            "le marché recrute des profils, pas des diplômes 📈\nsoft skills, CV & stratégie — tout ce qu'il faut savoir pour s'imposer dans la finance de demain avec Dr. Nabila Hamdoun 🎙️\n13 mai · 14h · Amphi 4 | entrée libre · places limitées 🔒 #ClubFintech #ENSIAS",
            $imgs('fintech', ['post2_img1.jpg']),
            Carbon::parse('2026-05-13 12:00:00')
        );

        $create($g,
            "✨ Aïd Moubarak à toutes et à tous ! ✨\nLe club FinTech vous souhaite une fête pleine de partage, de sérénité et de nouvelles idées. Que cette célébration inspire l'innovation et la solidarité. Bon Aïd 💜💙",
            $imgs('fintech', ['post3_img1.jpg']),
            Carbon::now()->subDays(33)
        );

        $create($g,
            "✨ Revivons ensemble les meilleurs moments ✨\nNotre cérémonie de clôture s'est achevée sur une énergie incroyable ⚡🎉\nUn immense bravo à nos gagnants 🏆\nMerci à nos sponsors : PayLogic – Sponsor officiel 🤝 | Casablanca Finance City – Sponsor Gold 🌟",
            $imgs('fintech', ['post4_img1.jpg', 'post4_img2.jpg', 'post4_img3.jpg',
                              'post4_img4.jpg', 'post4_img5.jpg', 'post4_img6.jpg',
                              'post4_img7.jpg', 'post4_img8.jpg']),
            Carbon::now()->subDays(45)
        );

        // ── Club Forum GENI ───────────────────────────────────────────────────
        $g = $group('club-forum-genie');

        $create($g,
            "Le Forum GENI ENSIAS vous invite à une session exclusive de Speed Dating avec des entreprises leaders du marché !\n• Rencontrer directement des recruteurs | • Présenter votre profil | • Décrocher un stage ou élargir votre réseau",
            $imgs('forum-geni', ['post1_img1.jpg']),
            Carbon::parse('2026-04-25 10:00:00')
        );

        $create($g,
            "Le Speed Dating PFA, organisé par le Forum GENI ENSIAS, se tiendra le mercredi 29 avril à 13h00 au Grand Amphi.\nÉchangez directement avec des entreprises partenaires et décrochez des projets de fin d'année enrichissants. #forumgeniensias #pfa",
            $imgs('forum-geni', ['post2_img1.jpg']),
            Carbon::parse('2026-04-26 09:00:00')
        );

        $create($g,
            "BCG X convie les ENSIASTES présélectionnés aux entretiens finaux. Rendez-vous le 26 novembre.",
            $imgs('forum-geni', ['post3_img1.jpg']),
            Carbon::parse('2025-11-25 10:00:00')
        );

        $create($g,
            "Nous avons le plaisir d'annoncer Morocco World News en tant que Sponsor Média officiel de la 21ᵉ édition du Forum Génie Entreprises (GENI 21) 🎉\n📅 15–16 octobre | 📍 ENSIAS",
            $imgs('forum-geni', ['post4_img1.jpg']),
            Carbon::parse('2025-10-10 10:00:00')
        );

        // ── Club House of Art ─────────────────────────────────────────────────
        $g = $group('club-house-of-art');

        $create($g,
            "🎤 ENSIAS TALENT SHOW — April 24\nThe stage is set. The lights are ready.\n✨ Meet the judges who will decide who rises… and who fades.\n🏆 A BIG PRIZE awaits the one who owns the stage.\n📍 ENSIAS | 📅 April 24 #TalentShow",
            $imgs('house-of-art', ['post1_img1.jpg']),
            Carbon::parse('2026-04-20 10:00:00')
        );

        $create($g,
            "Wishing you and your loved ones a joyful and blessed Ramadan! ❤️‍🩹🤍",
            $imgs('house-of-art', ['post2_img1.jpg']),
            Carbon::now()->subDays(55)
        );

        $create($g,
            "🔥 MINI-JOURNÉE ENSIAS 🔥\n📅 19 • 20 • 21 Décembre\n🎭 Talent show | ⚽ Tournois sportifs | 🕵️‍♂️ Treasure hunt | 🎧 DJ set | 🔥 Show ultras | 🏆 Finale & remise des prix\n#MiniJourneeENSIAS #HouseOfArt",
            $imgs('house-of-art', ['post3_img1.jpg', 'post3_img2.jpg']),
            Carbon::parse('2025-12-18 10:00:00')
        );

        $create($g,
            "Fun and laughter all night long 🤭\nJoin us at \"AGORA BY NIGHT\" where a variety of games 🎲, immaculate vibes ✨, and unforgettable moments 🪇 are awaiting!\n#HouseOfArts #ensias #agorabynight",
            $imgs('house-of-art', ['post4_img1.jpg']),
            Carbon::now()->subDays(48)
        );

        $create($g,
            "🎬✨ Movie Night: Screening of TAG! ✨🎬\n🕤 Thursday, November 14, at 9:30 PM | 📍 Grand Amphitheater\nBring your friends and don't miss this comedy packed with twists and turns! 🍿🎥",
            $imgs('house-of-art', ['post5_img1.jpg']),
            Carbon::parse('2025-11-14 21:00:00')
        );

        // ── Club INSEC ────────────────────────────────────────────────────────
        $g = $group('insec');

        $create($g,
            "Injection — Explained! 🛡️\nWeekly cybersecurity concept series with INSEC — Each week, we break down one essential term to help you level up your understanding of cyber & cloud security.\n💡 Stay curious. Stay secure. #INSEC #CyberSecurity",
            $imgs('insec', ['post1_img1.jpg', 'post1_img2.jpg', 'post1_img3.jpg',
                            'post1_img4.jpg', 'post1_img5.jpg', 'post1_img6.jpg',
                            'post1_img7.jpg', 'post1_img8.jpg']),
            Carbon::now()->subDays(25)
        );

        $create($g,
            "Access Trojan — Explained! 🛡️\nWeekly cybersecurity concept series with INSEC.\n💡 Stay curious. Stay secure. #INSEC #CyberSecurity #CloudSecurity",
            $imgs('insec', ['post2_img1.jpg', 'post2_img2.jpg', 'post2_img3.jpg',
                            'post2_img4.jpg', 'post2_img5.jpg', 'post2_img6.jpg',
                            'post2_img7.jpg', 'post2_img8.jpg']),
            Carbon::now()->subDays(18)
        );

        $create($g,
            "Macro Attack — Explained! 🛡️\nWeekly cybersecurity concept series with INSEC.\n💡 Stay curious. Stay secure. #INSEC #CyberSecurity #InfoSec",
            $imgs('insec', ['post3_img1.jpg', 'post3_img2.jpg', 'post3_img3.jpg',
                            'post3_img4.jpg', 'post3_img5.jpg', 'post3_img6.jpg',
                            'post3_img7.jpg']),
            Carbon::now()->subDays(11)
        );

        $create($g,
            "Here is a sneak peek at this year's MCSC winners 🎖\nCongratulations to the first three teams! To everyone else who participated: thank you for your hard work and dedication. #insec #mcsc #ctf",
            $imgs('insec', ['post4_img1.jpg', 'post4_img2.jpg']),
            Carbon::now()->subDays(60)
        );

        // ── Club Japonais ─────────────────────────────────────────────────────
        $g = $group('club-japonais');

        $create($g,
            "From culture to connection ✨ Japan Day was truly unforgettable. A big thank you to all our visitors and stands! You made it come alive.\nSpecial thanks to our partner @shotakume. #japan #japanday #ensias",
            $imgs('japonais', ['post1_img1.jpg', 'post1_img2.jpg', 'post1_img3.jpg',
                               'post1_img4.jpg', 'post1_img5.jpg', 'post1_img6.jpg',
                               'post1_img7.jpg', 'post1_img8.jpg', 'post1_img9.jpg',
                               'post1_img10.jpg', 'post1_img11.jpg', 'post1_img12.jpg',
                               'post1_img13.jpg', 'post1_img14.jpg', 'post1_img15.jpg',
                               'post1_img16.jpg', 'post1_img17.jpg', 'post1_img18.jpg',
                               'post1_img19.jpg', 'post1_img20.jpg']),
            Carbon::parse('2026-04-20 20:00:00')
        );

        $create($g,
            "🏴‍☠️ This year, Club Japonais ENSIAS invites you to set sail on a new adventure filled with surprises, new activities, and unforgettable moments.\n📍 ENSIAS | 🗓 April 19, 2026 | ⏰ Starting from 10:00 AM\nIn collaboration with Shotaku ⚓ #JapanDay",
            $imgs('japonais', ['post2_img1.jpg']),
            Carbon::parse('2026-04-18 10:00:00')
        );

        $create($g,
            "Where creativity comes to life 🎨✨\nFrom stunning cosplay makeup to live sketches, digital masterpieces, handmade chains, posters & stickers — our artists are ready to turn Japan Day into an experience you won't forget 💥",
            $imgs('japonais', ['post3_img1.jpg']),
            Carbon::parse('2026-04-17 10:00:00')
        );

        $create($g,
            "Art competition is finally here 🎨🖼️\nBe on time and don't bring any art supplies, it's all on us ✨\nOnly pure talents will compete and we're looking forward to see who will take the prize 🔥 #art #competition #japan",
            $imgs('japonais', ['post4_img1.jpg']),
            Carbon::parse('2026-04-16 10:00:00')
        );

        // ── Club Neurodynamics ────────────────────────────────────────────────
        $g = $group('club-neurodynamics');

        $create($g,
            "We are proud to announce NeuroDynamics' participation in the 13th Science Festival! 🧠🤖\nFrom Artificial Intelligence to Robotics and intelligent systems, our mission is to inspire curiosity and empower the next generation of innovators.\n📍 Oujda | 📅 February 12–14",
            $imgs('neurodynamics', ['post1_img1.jpg']),
            Carbon::parse('2026-02-10 10:00:00')
        );

        $create($g,
            "THIS WEDNESDAY!\n• Introduction to Classification in Machine Learning\n• Building Autonomous Robotics from Motors to Obstacle Avoidance\nBe there!",
            $imgs('neurodynamics', ['post2_img1.jpg', 'post2_img2.jpg', 'post2_img3.jpg']),
            Carbon::now()->subDays(30)
        );

        $create($g,
            "🚨 The exhibition is OPEN!\nCome explore the amazing projects our teams have been building all weekend. The final pitches kick off at 2 PM.\n#ai #robotics #machinelearning #hackathon #marc",
            $imgs('neurodynamics', ['post3_img1.jpg']),
            Carbon::now()->subDays(28)
        );

        $create($g,
            "ENSIAS NeuroDynamics Club wishes @adei.ensias a very happy 13th anniversary 🎉\nHere's to more success, innovation, and impact 🚀✨",
            $imgs('neurodynamics', ['post4_img1.jpg']),
            Carbon::now()->subDays(45)
        );

        // ── Club Quraan ───────────────────────────────────────────────────────
        $g = $group('club-quraan');

        $create($g,
            "يضرب لكم نادي القرآن الكريم موعدًا مع لقاءٍ من طرازٍ خاص، يلامس مرحلةَ الشباب التي يُسأل عنها العبد يوم القيامة.\nفي هذا اللقاء، نرتشف مواعظ بليغة يجود بها علينا فضيلة الأستاذ ياسين العمري.\n#نادي_القرآن_الكريم",
            $imgs('quraan', ['post1_img1.jpg']),
            Carbon::now()->subDays(20)
        );

        $create($g,
            "بلوغُ شهرِ رمضانَ وصيامُه نعمةٌ عظيمةٌ على من أقدره الله عليه.\nتتشرف أسرة نادي القرآن الكريم باستضافة الأستاذ يونس ريحان في محاضرة: «نسائم الإيمان في شهر الغفران»\n#نادي_القرآن_الكريم🧡💚",
            $imgs('quraan', ['post2_img1.jpg']),
            Carbon::now()->subDays(55)
        );

        $create($g,
            "رجال عاشوا للآخرة، فبقي ذكرهم في الدنيا",
            $imgs('quraan', ['post3_img1.jpg', 'post3_img2.jpg', 'post3_img3.jpg',
                              'post3_img4.jpg', 'post3_img5.jpg', 'post3_img6.jpg',
                              'post3_img7.jpg', 'post3_img8.jpg']),
            Carbon::now()->subDays(40)
        );

        $create($g,
            "محاضرة تُسلّط الضوء على واقع الاستثمار في ظل المالية الإسلامية مع الأستاذ الدكتور محمد طلال الحلو.\n📅 الاثنين 2 فبراير 2026 | 🕡 20:30 | 📍 المدرج الكبير - ENSIAS\n#نادي_القرآن_الكريم🧡💚",
            $imgs('quraan', ['post4_img1.jpg']),
            Carbon::parse('2026-02-02 20:30:00')
        );

        $create($g,
            "لقاء يُثري العقل، ويُنير البصيرة، ويُعيد ترتيب الأسئلة في أفقٍ أوسع من الفهم والإدراك.\nكونوا في الموعد. #نادي_القرآن_الكريم🧡💚",
            $imgs('quraan', ['post5_img1.jpg']),
            Carbon::now()->subDays(15)
        );

        // ── Club Sportif ──────────────────────────────────────────────────────
        $g = $group('club-sportif');

        $create($g,
            "Get ready for a day full of energy ⚡, team spirit 🤝, and unforgettable moments! ENSIAS Sports Day is all about bonding, excitement, and pure fun! #clubsportif #ensias #sportday",
            $imgs('sportif', ['post1_img1.jpg']),
            Carbon::now()->subDays(22)
        );

        $create($g,
            "يتقدّم النادي الرياضي إليكم بأبهى التهاني بمقدم عيد الفطر المبارك. جعله الله عليكم عيدًا ميمونًا محفوفًا باليُمن والبركات. وكل عام وأنتم بخير! #ramadan #clubsportif",
            $imgs('sportif', ['post2_img1.jpg']),
            Carbon::now()->subDays(58)
        );

        $create($g,
            "🏆 Ramadan Tournament Champions 🌙\nCongratulations to the winners! Well deserved ⚽👏 #ensias #clubsportif #tournoiramadan",
            $imgs('sportif', ['post3_img1.jpg', 'post3_img2.jpg']),
            Carbon::now()->subDays(50)
        );

        $create($g,
            "⚽️ Week 1 rankings ⚽️\n#ensias #football #clubsportif #ensiasclubs #sport #match",
            $imgs('sportif', ['post4_img1.jpg']),
            Carbon::now()->subDays(52)
        );

        $create($g,
            "Everyone's welcome — all levels, all sports! 🌟\nJoin the Sports Club and be part of something exciting 💪\nLet's make this season unforgettable! ⚽🏀🏐 #clubsportif",
            $imgs('sportif', ['post5_img1.jpg']),
            Carbon::now()->subDays(65)
        );

        $this->command->info('ClubPostsSeeder: done.');
    }
}
