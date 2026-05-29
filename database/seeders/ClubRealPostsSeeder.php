<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClubRealPostsSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = [
            // ─── BRIDGE ───────────────────────────────────────────────
            [
                'user_id'   => '78b4e1b2-2020-4c11-b109-2f9df54bc85b',
                'groupe_id' => '019e2060-c73b-73b3-b42d-f21e7b5f7cae',
                'folder'    => 'bridge',
                'posts'     => [
                    ['contenu' => "New leaders, same spirit ✨🧡\nAfter much anticipation, it's time to meet the 15th pilotage team of Bridge Club 🌉— carrying forward a legacy of excellence, connection, and ambition. A big thanks to the previous team — you paved the way.", 'images' => 7],
                    ['contenu' => "From inspiration to innovation 💡✨\n\"Bridge to IT\" was more than just an event, it was a space to learn, connect, and grow together.\n\nHuge thanks to everyone who joined us for the Orientation & Skills Workshop Series, proudly organized by ENSIAS IT Club × ENSIAS Bridge💙🧡. #EITC #Bridge #SkillsWorkshop", 'images' => 3],
                    ['contenu' => "Tonight's workshop is all about Data Engineering & Apache Spark with a hands-on session led by Rida Massoui, Data Engineer & Databricks Certified @ Amadeus.\n\n📅 19/05\n🕖 19:00\n📍 Salle Rouge – ENSIAS\n\n#BridgeENSIAS #DataEngineering", 'images' => 1],
                    ['contenu' => "Tonight we kick off the first workshop of our event series with an exciting session on Software Engineering & Agentic AI led by El Mehdi Assali, Senior Software Engineer @ BCG X.\n\n📅 18/05 🕖 19:00 📍 Salle Rouge – ENSIAS\n\n#BridgeENSIAS #SoftwareEngineering #GenAI", 'images' => 1],
                    ['contenu' => "Big collab alert 🤝\n\nIT × Bridge ENSIAS are joining forces to bring you the ultimate IT orientation experience!\n\n🗓 May 18–20 | 3 Days – 3 Tracks\n• Software Engineering\n• Data Engineering\n• Project Studio\n\n⚠️ Limited places available!", 'images' => 2],
                ],
            ],

            // ─── CINDH ────────────────────────────────────────────────
            [
                'user_id'   => 'a7b5a855-ab04-49be-b61a-284a8b7032ce',
                'groupe_id' => '019e2060-c75e-71b3-b303-e00ba54cc7a4',
                'folder'    => 'cindh',
                'posts'     => [
                    ['contenu' => "في ليلةٍ من ليالي العيد، جلس طفلٌ صغير ينتظر نصيبه من الفرح... بمساهمتكم، يمكن أن تصل الأضاحي والكسوة والقفف الغذائية إلى بيوتٍ تنتظر. 💙💛🐑", 'images' => 1],
                    ['contenu' => "مع اقتراب أيام عيد الأضحى المبارك، يسرّ نادينا الخيري CINDH أن يتقدم لكم بأطيب التهاني. تقبل الله منكم، ودمتم سالمين، وعيد مبارك سعيد 🌙", 'images' => 1],
                    ['contenu' => "«أحبُّ الناسِ إلى اللهِ تَعالَى أنفَعُهم للناسِ»\n\nتستمر حملة فرحة العيد في نادينا بفتح أبواب الخير واستقبال عطاءاتكم لتوفيرِ الأضاحي للأسرِ الأشدِّ فاقة وحاجة.", 'images' => 1],
                    ['contenu' => "بفضل الله وبفضل تبرعاتكم، تمكّنا من جمع ثمن ثلاث عشرة أضحية، ولا يزال الخيرُ مستمرًّا. رزقكم الله بقدر ما تصدّقتم أضعافًا. 🌙", 'images' => 1],
                    ['contenu' => "كل بذرة خير يغرسها النادي توضع بعناية. تم تجهيز بئر تمامة لضمان اشتغاله، إضافة إلى العمل على بناء كتاب بدوار إكيس لدعم تربية وتعليم الأطفال.", 'images' => 16],
                ],
            ],

            // ─── EITC ─────────────────────────────────────────────────
            [
                'user_id'   => '62c522e1-e53a-4158-8df1-10af6d3e0721',
                'groupe_id' => '019e2060-c723-70c6-8626-09e1f1d94e2c',
                'folder'    => 'eitc',
                'posts'     => [
                    ['contenu' => "Training – Workshop Alert!\n\nENSIAS IT Club invites you to a hands-on training workshop on Setting up a Web Service implementing Link Forwarding 💻🔗\n\n👨‍🏫 Guest Speaker: Prof. Hellman\n🎓 PhD, IMT & Former Senior Software Engineer at Microsoft\n📅 12/03 - 22:30", 'images' => 1],
                    ['contenu' => "Want to get started in web development? 👨‍💻✨\n\nJoin us for an interactive training workshop with Badreddine Aiz, Software Developer 🚀\n📅 12/02/2025 ⏰ 7:30 PM 📍 Salle A5\n\n#ENSIAS #ENSIAS_IT_Club #WebDev #EITC", 'images' => 1],
                    ['contenu' => "Join us this Friday at 7:00 PM for the announcement of our hackathon theme! 🖥️💡\nWe will also open the registration form.\n#Hackathon #Innovation #ENSIAS", 'images' => 1],
                    ['contenu' => "Step into the universe of clubs at ENSIAS! This Monday at 20:00, The Seven will guide you through the galaxy of opportunities with EITC at the center.\n\n#TheSeven #ENSIAS #EITC #Together_we_grow_in_tech", 'images' => 1],
                    ['contenu' => "🎮 IT ESCAPE Game Competition: The Winners Are Here! 🚀\n\nHuge congrats to the champions of IT ESCAPE! 🏆 Thanks to our amazing sponsors, RIBATIS and Cnexia, for making it happen.💚\n\n#ITSCAPE #GamingChampions #ENSIAS", 'images' => 1],
                ],
            ],

            // ─── FOUNDERS ─────────────────────────────────────────────
            [
                'user_id'   => '5e2a558f-c7c5-4fa2-9930-d1b69b0deaf7',
                'groupe_id' => '019e2060-c789-70e5-aac0-8e1a072bd363',
                'folder'    => 'founders',
                'posts'     => [
                    ['contenu' => "Workshop — Design Thinking\n\nNous accueillerons Pr. Soukaina El Boujnouni pour un workshop autour du Design Thinking.\n\n📍 ENSIAS, Grand Amphi\n📅 13 May 2026 🕒 15:30\n\nPlaces limitées — inscription obligatoire via le lien en bio.", 'images' => 1],
                    ['contenu' => "🚀 Entrepreneurship Day — Ambition to Action\n\nSous le thème « Ambition to Action », l'événement réunira des entrepreneurs, investisseurs et acteurs de l'écosystème.\n\n« Comment passer d'une idée à un projet concret, crédible et exécutable au Maroc ? »", 'images' => 1],
                    ['contenu' => "WHO ARE WE ? We are not just a standard club.\nWe're building a complete startup ecosystem inside ENSIAS.\n\nAt EFC, we Inspire, Train & Launch.\n\nTurn your ideas into impact.", 'images' => 3],
                    ['contenu' => "🚀 A new era begins at ENSIAS.\n\nWe're proud to announce the official launch of the ENSIAS Founders Club : the first club at ENSIAS entirely dedicated to startups and innovation. Come tonight at 22:30 in GA 💛", 'images' => 1],
                    ['contenu' => "Nous aurons l'honneur de recevoir M. Youssef Rkaissi, CEO de RKICY Technology, pour la 1ʳᵉ édition de Founders Talk.\n\n📍 ENSIAS — Grand Amphi\n📅 Jeudi 25 décembre 2025 🕡 18h30\n\n🚨 Places limitées", 'images' => 1],
                ],
            ],

            // ─── FINTECH ──────────────────────────────────────────────
            [
                'user_id'   => '1db68da3-8dc1-434e-b78c-f7fc738f0c37',
                'groupe_id' => '019e2060-c777-70c5-a29b-ba988cbcda63',
                'folder'    => 'fintech',
                'posts'     => [
                    ['contenu' => "FinTech Family تهنئكم بحلول عيد الأضحى المبارك، أعاده الله عليكم بالخير والبركات 🤍🌙🐑", 'images' => 1],
                    ['contenu' => "le marché recrute des profils, pas des diplômes 📈\nsoft skills, CV & stratégie avec Dr. Nabila Hamdoun 🎙️\n13 mai · 14h · Amphi 4 · entrée libre\n\n#ClubFintech #ENSIAS #Finance", 'images' => 1],
                    ['contenu' => "✨ Aïd Moubarak à toutes et à tous ! ✨\n\nLe club FinTech vous souhaite une fête pleine de partage et de nouvelles idées. Bon Aïd 💜💙", 'images' => 1],
                    ['contenu' => "✨ Revivons les meilleurs moments de notre cérémonie de clôture ⚡🎉\n\nMerci à nos sponsors :\n🔹 PayLogic – Sponsor officiel\n🔹 Casablanca Finance City – Sponsor Gold 🌟", 'images' => 8],
                ],
            ],

            // ─── FORUM GENIE ──────────────────────────────────────────
            [
                'user_id'   => '7dd60572-369c-4d52-9d0d-ae18a66b1c3f',
                'groupe_id' => '019e2060-c755-738a-8e1b-30588cbb6f06',
                'folder'    => 'forum',
                'posts'     => [
                    ['contenu' => "Le Forum GENI ENSIAS vous invite à une session exclusive de Speed Dating avec des entreprises leaders du marché !\n\n• Rencontrer des recruteurs\n• Présenter votre profil\n• Décrocher un stage", 'images' => 1],
                    ['contenu' => "Le Speed Dating PFA se tiendra le mercredi 29 avril à 13h00 au Grand Amphi de l'ENSIAS.\n\nÉchangez directement avec des professionnels du secteur IT.\n\n#forumgeniensias #ensias #pfa #speeddating", 'images' => 1],
                    ['contenu' => "BCG X convie les ENSIASTES présélectionnés aux entretiens finaux. Rendez-vous le 26 novembre.", 'images' => 1],
                    ['contenu' => "Morocco World News rejoint la 21ᵉ édition du Forum Génie Entreprises (GENI 21) en tant que Sponsor Média officiel 🎉\n\n📅 15–16 octobre 📍 ENSIAS", 'images' => 1],
                ],
            ],

            // ─── HOUSE OF ART ─────────────────────────────────────────
            [
                'user_id'   => '939c183d-991f-4d91-bd07-6c412375bd80',
                'groupe_id' => '019e2060-c77f-71b1-8839-b3ab04d63958',
                'folder'    => 'houseart',
                'posts'     => [
                    ['contenu' => "🎤 ENSIAS TALENT SHOW — April 24\nThe stage is set. The lights are ready.\n\n🏆 A BIG PRIZE awaits the one who owns the stage.\n📍 ENSIAS 📅 April 24\n\n#ENSIAS #TalentShow #WinTheCrown", 'images' => 1],
                    ['contenu' => "Wishing you and your loved ones a joyful and blessed Ramadan! ❤️‍🩹🤍", 'images' => 1],
                    ['contenu' => "🔥 MINI-JOURNÉE ENSIAS 🔥\nTrois jours. Une seule ambiance.\n\n📅 19 • 20 • 21 Décembre\n🎭 Talent show ⚽ Tournois 🕵️ Treasure hunt 🎧 DJ set\n\n#MiniJourneeENSIAS #HouseOfArt", 'images' => 2],
                    ['contenu' => "Join us at \"AGORA BY NIGHT\" where games 🎲, immaculate vibes ✨️ and unforgettable moments 🪇 await!\n\n#HouseOfArts #ensias #agorabynight", 'images' => 1],
                    ['contenu' => "🎬✨ Movie Night: Screening of TAG!\n\n🕤 Thursday, November 14, at 9:30 PM\n📍 Grand Amphitheater\n\nBring your friends! 🍿🎥", 'images' => 1],
                ],
            ],

            // ─── INSEC ────────────────────────────────────────────────
            [
                'user_id'   => 'e0ff1171-da9a-4757-bd33-497e5b93a1e6',
                'groupe_id' => '019e2060-c72c-72a6-ab83-66633e7ea558',
                'folder'    => 'insec',
                'posts'     => [
                    ['contenu' => "Injection — Explained!\n\nWelcome to our weekly cybersecurity concept series with INSEC 🛡️\nEach week, we break down one essential term to help you level up your understanding of cyber & cloud security.\n\n#INSEC #CyberSecurity #WeeklyConcepts", 'images' => 8],
                    ['contenu' => "Access Trojan — Explained!\n\nWelcome to our weekly cybersecurity concept series with INSEC 🛡️\nEach week, we break down one essential term to help you level up your understanding of cyber & cloud security.\n\n#INSEC #CyberSecurity #WeeklyConcepts", 'images' => 8],
                    ['contenu' => "Macro Attack — Explained!\n\nWelcome to our weekly cybersecurity concept series with INSEC 🛡️\nEach week, we break down one essential term to help you level up your understanding of cyber & cloud security.\n\n#INSEC #CyberSecurity #WeeklyConcepts", 'images' => 7],
                    ['contenu' => "Here is a sneak peek at this year's MCSC winners.\n\nCongratulations to the first three teams! 🎖\nThank you for your hard work and dedication.\n\n#insec #mcsc #ctf", 'images' => 2],
                ],
            ],

            // ─── JAPONAIS ─────────────────────────────────────────────
            [
                'user_id'   => 'e6ecca98-6b2f-4b7e-a741-a2df5633eb11',
                'groupe_id' => '019e0eb8-da7c-7252-b301-47772cf80027',
                'folder'    => 'japonnais',
                'posts'     => [
                    ['contenu' => "From culture to connection ✨ Japan Day was truly unforgettable!\nA big thank you to all our visitors and stands!\nSpecial thanks to our partner @shotakume.\n#japan #japanday #ensias", 'images' => 20],
                    ['contenu' => "🏴‍☠️ Club Japonais ENSIAS invites you to set sail on a new adventure!\n\n📍 ENSIAS, Madinat Al Irfane, Rabat\n🗓 April 19, 2026 ⏰ 10:00 AM\n\n#JapanDay #AnimeMorocco #OtakuCulture", 'images' => 1],
                    ['contenu' => "Where creativity comes to life 🎨✨\nFrom cosplay makeup to live sketches, digital masterpieces, handmade chains — our artists are ready to turn Japan Day into an experience you won't forget. 👀💥", 'images' => 1],
                    ['contenu' => "The art competition is finally here 🎨🖼️\nBe on time — it's all on us ✨\nOnly pure talents will compete 🔥\n#art #competition #japan #event", 'images' => 1],
                ],
            ],

            // ─── NEURODYNAMICS ────────────────────────────────────────
            [
                'user_id'   => '196f640e-ae8a-403e-8528-7a3652b5ae50',
                'groupe_id' => '019e2060-c74c-7341-9506-91592435ef5b',
                'folder'    => 'neurodynamics',
                'posts'     => [
                    ['contenu' => "We are proud to announce NeuroDynamics' participation in the 13th Science Festival! 🧠🤖\n\n📍 Oujda 📅 February 12–14\n\nFrom AI to Robotics — inspiring the next generation of innovators.", 'images' => 1],
                    ['contenu' => "THIS WEDNESDAY !\n\n📌 Introduction to Classification in Machine Learning\n📌 Building Autonomous Robotics from Motors to Obstacle Avoidance\n\nBe there !", 'images' => 3],
                    ['contenu' => "🚨 The exhibition is OPEN!\nCome explore the projects our teams have been building all weekend.\n\n#ai #robotics #machinelearning #hackathon #marcv1", 'images' => 1],
                    ['contenu' => "ENSIAS NeuroDynamics Club wishes @adei.ensias a very happy 13th anniversary 🎉\nHere's to more success, innovation, and impact 🚀✨", 'images' => 1],
                ],
            ],

            // ─── QURAAN ───────────────────────────────────────────────
            [
                'user_id'   => '67e5f4ff-e512-4ac0-a7d2-37a128328013',
                'groupe_id' => '019e2060-c733-73f9-8858-e8abd96cb2e5',
                'folder'    => 'quraan',
                'posts'     => [
                    ['contenu' => "يضرب لكم نادي القرآن الكريم موعدًا مع لقاءٍ خاص مع فضيلة الأستاذ ياسين العمري، بما يوقظ القلوب ويشحذ الهمم.\n#نادي_القرآن_الكريم", 'images' => 1],
                    ['contenu' => "تتشرف أسرة نادي القرآن الكريم باستضافة الأستاذ يونس ريحان في محاضرة:\n«نسائم الإيمان في شهر الغفران»\n\nندعوكم للحضور يوم الثلاثاء إن شاء الله.\n#نادي_القرآن_الكريم🧡💚", 'images' => 1],
                    ['contenu' => "رجال عاشوا للآخرة، فبقي ذكرهم في الدنيا 🌿", 'images' => 8],
                    ['contenu' => "محاضرة حول واقع الاستثمار في ظل المالية الإسلامية مع الأستاذ الدكتور محمد طلال الحلو.\n\n📅 الاثنين 2 فبراير 2026 🕡 20:30\n📍 المدرج الكبير - ENSIAS\n#نادي_القرآن_الكريم🧡💚", 'images' => 1],
                    ['contenu' => "لقاء يُثري العقل، ويُنير البصيرة.\nكونوا في الموعد.\n#نادي_القرآن_الكريم🧡💚", 'images' => 1],
                ],
            ],

            // ─── SPORTIF ──────────────────────────────────────────────
            [
                'user_id'   => '6ff8da31-63b6-4870-8381-a69442febf13',
                'groupe_id' => '019e2060-c76f-7167-87c5-298aed4a454b',
                'folder'    => 'sportif',
                'posts'     => [
                    ['contenu' => "Get ready for a day full of energy ⚡, team spirit 🤝, and unforgettable moments!\nENSIAS Sports Day is all about bonding, excitement, and pure fun!\n#clubsportif #ensias #sportday", 'images' => 1],
                    ['contenu' => "يتقدّم النادي الرياضي بأبهى التهاني بمقدم عيد الفطر المبارك. جعله الله عيدًا ميمونًا وكل عام وأنتم بخير!\n#ramadan #clubsportif #ensias", 'images' => 1],
                    ['contenu' => "🏆 Ramadan Tournament Champions 🌙\nCongratulations to the winners! Well deserved. ⚽👏\n#ensias #clubsportif #tournoiramadan", 'images' => 2],
                    ['contenu' => "⚽️ Week 1 rankings ⚽️\n\n#ensias #football #clubsportif #sport #match", 'images' => 1],
                    ['contenu' => "Everyone's welcome — all levels, all sports! 🌟\nJoin the Sports Club and be part of something exciting 💪\n\nLet's make this season unforgettable! ⚽🏀🏐", 'images' => 1],
                ],
            ],
        ];

        $totalPubs   = 0;
        $totalMedias = 0;

        foreach ($clubs as $club) {
            foreach ($club['posts'] as $index => $post) {
                $pubId       = Str::uuid()->toString();
                $publishedAt = Carbon::now()->subDays(rand(1, 90))->subHours(rand(0, 23));
                $postNum     = $index + 1;

                DB::table('publications')->insert([
                    'id'               => $pubId,
                    'contenu'          => $post['contenu'],
                    'typeMedia'        => null,
                    'statutValidation' => 'Valide',
                    'user_id'          => $club['user_id'],
                    'groupe_id'        => $club['groupe_id'],
                    'publishedAt'      => $publishedAt,
                    'created_at'       => $publishedAt,
                    'updated_at'       => $publishedAt,
                ]);
                $totalPubs++;

                for ($imgIndex = 1; $imgIndex <= $post['images']; $imgIndex++) {
                    DB::table('post_media')->insert([
                        'id'             => Str::uuid()->toString(),
                        'publication_id' => $pubId,
                        'url'            => "/storage/clubs/{$club['folder']}/post{$postNum}_img{$imgIndex}.jpg",
                        'type'           => 'image',
                        'thumbnail_url'  => null,
                        'order'          => $imgIndex,
                        'created_at'     => $publishedAt,
                        'updated_at'     => $publishedAt,
                    ]);
                    $totalMedias++;
                }
            }
            echo "OK {$club['folder']}\n";
        }

        echo "\n====================================\n";
        echo "Publications : {$totalPubs}\n";
        echo "Medias       : {$totalMedias}\n";
        echo "====================================\n";
    }
}
