<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'title' => 'Bitcoin breaks a new threshold: what are the implications for 2025?',
                'slug' => Str::slug('Bitcoin breaks a new threshold: what are the implications for 2025?'),
                'category' => 'News',
                'summary' => 'Bitcoin surpasses a new psychological level. Analysis of macroeconomic factors influencing this rise and possible scenarios for the coming months.',
                'content' => 'Bitcoin surpasses a new psychological level. This article analyses macroeconomic drivers, investor sentiment and potential scenarios for 2025.\n\n[Full analysis here]',
                'tags' => ['Bitcoin', 'market', 'analysis', 'trend'],
                'image' => 'images/btc_threshold.jpg',
                'published_at' => '2025-12-03 08:00:00',
            ],
            [
                'title' => 'Ethereum technical analysis: key levels to watch this week',
                'slug' => Str::slug('Ethereum technical analysis: key levels to watch this week'),
                'category' => 'Technical Analysis',
                'summary' => 'ETH shows an interesting chart structure. Here are the critical zones, major supports and next resistances.',
                'content' => 'Ethereum presents an interesting chart formation. We identify supports, resistances and potential trade setups for the coming sessions.\n\n[Charts and levels]',
                'tags' => ['ETH', 'trading', 'support/resistance'],
                'image' => 'images/eth_chart.jpg',
                'published_at' => '2025-12-03 09:00:00',
            ],
            [
                'title' => 'Understanding altcoins: a complete beginner\'s guide',
                'slug' => Str::slug("Understanding altcoins: a complete beginner's guide"),
                'category' => 'Beginner Guides',
                'summary' => 'Definition, purpose, risks, and projects to follow: everything you need to know before investing in altcoins.',
                'content' => 'This guide explains what altcoins are, why they exist, how to evaluate projects, and the risks associated with them.\n\n[Beginner checklist]',
                'tags' => ['Altcoin', 'crypto', 'beginner'],
                'image' => 'images/altcoins.jpg',
                'published_at' => '2025-12-03 10:00:00',
            ],
            [
                'title' => 'How blockchain technology really works',
                'slug' => Str::slug('How blockchain technology really works'),
                'category' => 'Blockchain',
                'summary' => 'A simplified and illustrated explanation of how the blockchain works: blocks, hashing and consensus.',
                'content' => 'A plain-language explanation of blockchain fundamentals: block structure, hashing, and consensus mechanisms such as PoW and PoS.\n\n[Illustrations and examples]',
                'tags' => ['blockchain', 'proof of work', 'proof of stake'],
                'image' => 'images/blockchain_network.jpg',
                'published_at' => '2025-12-03 11:00:00',
            ],
            [
                'title' => 'Top 5 cryptocurrencies to watch in 2025',
                'slug' => Str::slug('Top 5 cryptocurrencies to watch in 2025'),
                'category' => 'Trends',
                'summary' => 'A selection of the most promising projects based on adoption, innovation and growth potential.',
                'content' => 'We highlight five projects that show strong fundamentals, adoption potential, and unique use-cases that could outperform in 2025.\n\n[Project list and reasoning]',
                'tags' => ['trend', 'crypto', 'top 5'],
                'image' => 'images/top_crypto.jpg',
                'published_at' => '2025-12-03 12:00:00',
            ],
        ];

        $admin = User::where('role', 'ADMIN')->first();
        foreach ($data as $item) {
            if ($admin) $item['user_id'] = $admin->id;
            BlogPost::updateOrCreate([ 'slug' => $item['slug'] ], $item);
        }
    }
}
