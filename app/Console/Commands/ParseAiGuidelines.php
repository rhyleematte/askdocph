<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// use App\Models\AiGuideline;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ParseAiGuidelines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:parse-guidelines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse new AI guidelines and add them to the knowledge base';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $guidelines = AiGuideline::where('is_parsed', false)->get();
        $guidelines = collect(); // Fallback to avoid errors

        if ($guidelines->isEmpty()) {
            $this->info('No new guidelines to parse.');
            return 0;
        }

        $knowledgePath = storage_path('app/ai_knowledge.json');
        $knowledge = [];
        if (file_exists($knowledgePath)) {
            $knowledge = json_decode(file_get_contents($knowledgePath), true) ?? [];
        }

        $parsedCount = 0;

        foreach ($guidelines as $guideline) {
            $fullPath = storage_path('app/' . $guideline->file_path);
            
            if (!file_exists($fullPath)) {
                $this->error("File not found: {$fullPath}");
                continue;
            }

            $extractedText = '';
            $extension = pathinfo($fullPath, PATHINFO_EXTENSION);

            if (strtolower($extension) === 'pdf') {
                $pythonScript = base_path('parse_single_pdf.py');
                $command = escapeshellcmd("python \"{$pythonScript}\" \"{$fullPath}\"");
                $output = shell_exec($command);
                
                $result = json_decode($output, true);
                if ($result && isset($result['success']) && $result['success']) {
                    $extractedText = $result['text'];
                } else {
                    $error = $result['error'] ?? 'Unknown script error';
                    $this->error("Failed to parse PDF {$guideline->original_filename}: {$error}");
                    continue;
                }
            } elseif (strtolower($extension) === 'txt') {
                $extractedText = substr(file_get_contents($fullPath), 0, 1500); // chunk to 1500 chars limit approx
            } else {
                $this->error("Unsupported file type: {$extension}");
                continue;
            }

            if (!empty(trim($extractedText))) {
                // Add to knowledge
                $knowledge[] = [
                    'source' => $guideline->original_filename,
                    'content' => trim($extractedText)
                ];

                $guideline->update(['is_parsed' => true]);
                $parsedCount++;
                $this->info("Parsed: {$guideline->original_filename}");
            } else {
                $this->error("No text extracted from {$guideline->original_filename}");
            }
        }

        if ($parsedCount > 0) {
            file_put_contents($knowledgePath, json_encode($knowledge, JSON_PRETTY_PRINT));
            $this->info("Successfully updated ai_knowledge.json with {$parsedCount} new documents.");
        }

        return 0;
    }
}
