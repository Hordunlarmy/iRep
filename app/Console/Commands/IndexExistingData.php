<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IndexExistingData extends Command
{
    protected $signature = 'search:index-existing';
    protected $description = 'Index existing data from the database into SearchEngine';

    protected $searchEngine;

    public function __construct()
    {
        parent::__construct();
        $this->searchEngine = app('search');
    }

    public function handle(): void
    {

        $this->indexAccountsWithRepresentatives();
        $this->indexPosts();

    }

    /**
     * Fetch and index account data along with representatives.
     */
    private function indexAccountsWithRepresentatives(): void
    {
        try {
            $accountData = DB::select("
				SELECT a.id, a.name, a.photo_url, at.name AS account_type, a.location,
					s.name as state, lg.name as local_government,
					d.name as district, c.name as constituency,
					pt.name as party, p.title as position, a.created_at
				FROM accounts a
				LEFT JOIN representatives r ON a.id = r.account_id
				LEFT JOIN states s ON a.state_id = s.id
				LEFT JOIN local_governments lg ON a.local_government_id = lg.id
				LEFT JOIN positions p ON r.position_id = p.id
				LEFT JOIN constituencies c ON r.constituency_id = c.id
				LEFT JOIN parties pt ON r.party_id = pt.id
				LEFT JOIN districts d ON r.district_id = d.id
				LEFT JOIN account_types at ON a.account_type = at.id
			");

            // Convert data to an array format Meilisearch accepts
            $accountDataArray = json_decode(json_encode($accountData), true);
            $sortableAttributes = ['created_at', 'name', 'account_type'];
            $filterableAttributes = [
                'account_type', 'position', 'constituency', 'party',
                'district', 'state', 'local_government'
            ];

            // Index account data in Meilisearch
            $total = $this->searchEngine->indexData(
                indexName: 'accounts',
                data: $accountDataArray,
                sortableAttributes: $sortableAttributes,
                filterableAttributes: $filterableAttributes,
                primaryKey: 'id',
            );

            // Log the number of indexed records
            $this->info($total . ' accounts indexed successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to index accounts: ' . $e->getMessage());
        }
    }

    /**
     * Fetch and index posts data.
     */
    private function indexPosts(): void
    {
        // Fetch posts data from the database
        try {
            $postsData = DB::select("
				SELECT p.id, p.title, p.context, p.post_type, p.media,
				a.name AS author,
				a.id AS author_id,
				rep.name AS target_representative,
				pe.status,
				pe.signatures,
				pe.target_signatures,
				ewr.category,
				p.created_at
				FROM posts p
				LEFT JOIN petitions pe ON p.id = pe.post_id
				LEFT JOIN eye_witness_reports ewr ON p.id = ewr.post_id
				LEFT JOIN accounts a ON p.creator_id = a.id
				LEFT JOIN accounts rep ON pe.target_representative_id = rep.id
			");

            $postsDataArray = json_decode(json_encode($postsData), true);
            $sortableAttributes = ['created_at', 'title', 'post_type'];
            $filterableAttributes = ['status', 'category', 'post_type', 'author'];

            $total = $this->searchEngine->indexData(
                indexName: 'posts',
                data: $postsDataArray,
                sortableAttributes: $sortableAttributes,
                filterableAttributes: $filterableAttributes,
                primaryKey: 'id',
            );

            $this->info($total . ' Posts indexed successfully.');

        } catch (\Exception $e) {
            $this->error('Failed to index posts: ' . $e->getMessage());
        }
    }
}
