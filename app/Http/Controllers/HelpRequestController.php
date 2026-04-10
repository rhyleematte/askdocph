<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\HelpRequest;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Notification;
use App\Models\Message;
use App\Models\DoctorApplication;
use Illuminate\Support\Facades\Log;

class HelpRequestController extends Controller
{
    public function chat(Request $request)
    {
        $messages = $request->input('messages', []);
        // Check for sensitive topics first
        $sensitiveKeywords = [
            'suicide', 'self-harm', 'self harm', 'kill myself', 'kill', 'end my life',
            'want to die', 'hurting myself', 'cutting', 'overdose', 'harm myself',
            'suicidal', 'self injury', 'self-injury', 'self mutilation',
            'self-mutilation', 'suicidal thoughts', 'suicidal ideation',
            'harmful behaviors', 'hurting myself', 'ending it all', 'no reason to live',
            'can\'t go on', 'want to disappear', 'don\'t want to exist'
        ];

        // Check the last user message
        $lastMessage = end($messages);
        if ($lastMessage && $lastMessage['role'] === 'user') {
            $userText = strtolower($lastMessage['content']);
            foreach ($sensitiveKeywords as $keyword) {
                if (str_contains($userText, $keyword)) {
                    return response()->json([
                        'role' => 'assistant',
                        'content' => $this->crisisSupportMessage(),
                        'emotion' => 'High distress',
                        'topics' => ['crisis support', 'suicidal thoughts']
                    ]);
                }
            }
        }

        $knowledgeText = '';
        $knowledgePath = storage_path('app/ai_knowledge.json');
        if (file_exists($knowledgePath)) {
            $docs = json_decode(file_get_contents($knowledgePath), true) ?? [];
            $docs = array_values(array_filter($docs, function ($doc) {
                $haystack = strtolower(($doc['source'] ?? '') . ' ' . ($doc['content'] ?? ''));
                return !str_contains($haystack, 'india') && !str_contains($haystack, 'indian');
            }));
            if (!empty($docs)) {
                shuffle($docs);
                $selected = array_slice($docs, 0, 3);
                foreach($selected as $doc) {
                    $knowledgeText .= "[Source: " . $doc['source'] . "]\n" . $doc['content'] . "\n\n";
                }
            }
        }
        
        $systemPromptText = "You are an empathetic, friendly, and highly conversational AI mental health companion. Talk to the user like a caring human friend would, not like a robotic clinical screener.\n" .
        "Your primary role is to listen to the user, validate their feelings, and engage in a natural back-and-forth conversation.\n\n" .
        "CRITICAL INSTRUCTIONS:\n" .
        "1. Keep your replies very concise and brief, usually just 1 to 3 short sentences.\n" .
        "2. Do NOT sound robotic or clinical. Use natural, warm, and conversational language.\n" .
        "3. Ask thoughtful follow-up questions to understand how they are doing, but only one question at a time.\n" .
        "4. Base any factual information loosely on the provided context guidelines, but do not sound like you are just reading from a textbook.\n" .
        "5. The user is in the Philippines. When giving location-specific guidance, use Philippines-based context and resources, never India-specific agencies or hotlines.\n" .
        "6. If the user mentions self-harm, suicide, or immediate danger, urgently encourage them to call 911 in the Philippines or contact the NCMH Crisis Hotline: 1553, 0917-899-8727, 0966-351-4518, or 1800-1888-1553.\n" .
        "7. IMPORTANT DOCTOR MATCHING RULE: First, listen and converse. DO NOT immediately suggest a professional in the first few messages. ONLY when they explicitly ask for professional help, or when it becomes very clear through the conversation that they need a doctor's attention, should you suggest one.\n" .
        "8. Make use of a single relevant emoji occasionally to feel engaging.\n\n" .
        "Negative Prompts (DO NOT DO THESE):\n" .
        "- Do not provide medical diagnoses or prescribe medications.\n" .
        "- Do not write long, multi-paragraph essays or dump large lists of information.\n" .
        "- Do not claim to be a licensed therapist or medical professional.\n" .
        "- Do not share personal AI opinions or make assumptions about the user's condition.\n" .
        "- Do not mention Indian hotlines, Indian ministries, or India-specific services unless the user explicitly asks about India.\n\n" .
        "Context (from mental health database):\n" . $knowledgeText . "\n\n" .
        "Doctor Matching Execution: As instructed, only when you have conversed enough and it is evident they need professional help (e.g. Psychologist, Psychiatrist, Therapist, Counselor), you MUST provide a suggested title.\n\n" .
        "IMPORTANT - JSON OUTPUT ONLY:\n" .
        "You MUST respond entirely in valid JSON format. Return a JSON object with the following schema:\n" .
        "{\n" .
        "  \"reply\": \"Your conversational response directly to the user.\",\n" .
        "  \"suggested_title\": \"Single word like 'Psychologist' or 'Therapist' ONLY if recommending one, otherwise null.\",\n" .
        "  \"emotion\": \"The detected primary emotion of the user (e.g., 'Anxiety', 'Sadness', 'Relief', 'Neutral').\",\n" .
        "  \"topics\": [\"topic1\", \"topic2\"]\n" .
        "}";

        $systemPrompt = [
            'role' => 'system',
            'content' => $systemPromptText
        ];
        
        $apiMessages = array_merge([$systemPrompt], $messages);
        
        try {
            // Using standard OpenAI compatible format with Groq
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type' => 'application/json'
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                // Groq model
                'model' => 'llama-3.3-70b-versatile',
                'messages' => $apiMessages,
                'temperature' => 0.7,
                'max_tokens' => 1024,
                'response_format' => ['type' => 'json_object']
            ]);
            
            // If the /api/v1/ fails with 404
            if (!$response->successful() && $response->status() === 404) {
                // Mock the response so the UI flow doesn't break, since the chat endpoint might not exist yet on the server.
                return response()->json([
                    'role' => 'assistant',
                    'content' => "I understand what you're going through. Based on what you've shared, I suggest speaking with a Psychiatrist.",
                    'suggested_title' => 'Psychiatrist'
                ]);
            }
            
            $result = $response->json();
            
            if ($result && isset($result['choices'][0]['message']['content'])) {
                $rawContent = $result['choices'][0]['message']['content'];
                $parsed = json_decode($rawContent, true);
                
                if (json_last_error() === JSON_ERROR_NONE && isset($parsed['reply'])) {
                    $reply = $parsed['reply'];
                    $title = $parsed['suggested_title'] ?? null;
                    $emotion = $parsed['emotion'] ?? 'Neutral';
                    $topics = $parsed['topics'] ?? [];
                    
                    if ($title) {
                        return response()->json([
                            'role' => 'assistant',
                            'content' => "I understand what you're going through. Based on what you've shared, I suggest speaking with a " . $title . ".\n\n" . $reply,
                            'suggested_title' => $title,
                            'emotion' => $emotion,
                            'topics' => $topics
                        ]);
                    }
                    
                    return response()->json([
                        'role' => 'assistant',
                        'content' => $reply,
                        'emotion' => $emotion,
                        'topics' => $topics
                    ]);
                }
                
                // Fallback if not valid JSON
                return response()->json([
                    'role' => 'assistant',
                    'content' => $rawContent
                ]);
            }
            
            return response()->json([
                'role' => 'assistant',
                'content' => 'API Error (Status '.$response->status().'). Raw response: ' . $response->body()
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage());
            return response()->json([
                'role' => 'assistant',
                'content' => 'Could not connect to AI. Error: ' . $e->getMessage()
            ], 200);
        }
    }

    public function findDoctors(Request $request)
    {
        $title = $request->query('title', '');
        
        // Find approved doctors who are online and free to talk
        // We'll also try to match the title broadly via their doctorApplication
        $doctorsQuery = User::where('doctor_status', 'approved')
            ->where('last_active_at', '>=', now()->subMinutes(15)); // Automatically match doctors active in last 15 min
            
        $doctors = $doctorsQuery->with('doctorApplication')->get();
        
        // As a fallback or filter, ideally we'd filter by title
        if ($title) {
            $filtered = $doctors->filter(function($doc) use ($title) {
                // If the prompt is "Psychiatrist" we see if it's in their professional_titles
                $titles = $doc->professional_title ? strtolower($doc->professional_title) : '';
                return str_contains($titles, strtolower($title));
            });
            // If we found exact matches, use them, otherwise return any free doctors
            if ($filtered->count() > 0) {
                $doctors = $filtered;
            }
        }

        $result = $doctors->map(function($doc) {
            return [
                'id' => $doc->id,
                'name' => $doc->full_name,
                'avatar' => $doc->avatar_url,
                'title' => $doc->professional_title ?? 'Doctor'
            ];
        })->values();

        return response()->json(['doctors' => $result]);
    }

    public function requestConversation(Request $request)
    {
        $doctorId = $request->input('doctor_id');
        $title = $request->input('suggested_title');
        
        $helpRequest = HelpRequest::create([
            'user_id' => auth()->id(),
            'doctor_id' => $doctorId,
            'suggested_title' => $title,
            'status' => 'pending'
        ]);

        // Create a notification for the doctor
        Notification::create([
            'user_id' => $doctorId,
            'actor_id' => auth()->id(),
            'type' => 'help_request',
            'data' => [
                'message' => auth()->user()->full_name . " is requesting a " . $title . " chat.",
                'request_id' => $helpRequest->id,
                'suggested_title' => $title,
                'url' => url('/dashboard') // Point to dashboard where the approval panel is
            ]
        ]);

        return response()->json(['success' => true, 'request_id' => $helpRequest->id]);
    }
    
    public function getRequestStatus($id)
    {
        $helpRequest = HelpRequest::findOrFail($id);
        $convo = null;
        
        if ($helpRequest->status === 'accepted') {
            $convo = Conversation::where('type', 'direct')
                ->whereHas('participants', function($q) use ($helpRequest) {
                    $q->where('user_id', $helpRequest->user_id);
                })
                ->whereHas('participants', function($q) use ($helpRequest) {
                    $q->where('user_id', $helpRequest->doctor_id);
                })
                ->latest('conversations.updated_at')
                ->first();
        }
        
        return response()->json([
            'status' => $helpRequest->status,
            'conversation_id' => $convo ? $convo->id : null
        ]);
    }
    
    // For Doctors to View Their Pending Requests
    public function pendingRequests()
    {
        $requests = HelpRequest::with('user')
            ->where('doctor_id', auth()->id())
            ->where('status', 'pending')
            ->latest()
            ->get();
            
        return response()->json(['requests' => $requests]);
    }
    
    // For Doctor to Accept the Request
    public function acceptRequest($id)
    {
        $helpRequest = HelpRequest::findOrFail($id);
        
        if ($helpRequest->doctor_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $helpRequest->update(['status' => 'accepted']);
        
        // Check for existing direct conversation (prefer most recently active one)
        $convo = Conversation::where('type', 'direct')
            ->whereHas('participants', function($q) use ($helpRequest) {
                $q->where('user_id', $helpRequest->doctor_id);
            })
            ->whereHas('participants', function($q) use ($helpRequest) {
                $q->where('user_id', $helpRequest->user_id);
            })
            ->latest('conversations.updated_at')
            ->first();

        if (!$convo) {
            // Create conversation
            $convo = Conversation::create(['type' => 'direct']);
            
            ConversationParticipant::create([
                'conversation_id' => $convo->id,
                'user_id' => $helpRequest->user_id
            ]);
            
            ConversationParticipant::create([
                'conversation_id' => $convo->id,
                'user_id' => $helpRequest->doctor_id
            ]);
        } else {
            // Restore visibility if it was hidden/archived
            ConversationParticipant::where('conversation_id', $convo->id)
                ->where('user_id', $helpRequest->user_id)
                ->update(['deleted_at' => null]);
            ConversationParticipant::where('conversation_id', $convo->id)
                ->where('user_id', $helpRequest->doctor_id)
                ->update(['deleted_at' => null]);
        }

        // Send an automated greeting if it's not already the last message
        $lastMessage = Message::where('conversation_id', $convo->id)->latest()->first();
        $greetingBody = "Hello! I have accepted your request for a " . ($helpRequest->suggested_title ?? 'consultation') . ". How can I help you today?";
        
        if (!$lastMessage || $lastMessage->body !== $greetingBody) {
            Message::create([
                'conversation_id' => $convo->id,
                'sender_user_id' => auth()->id(),
                'message_type' => 'text',
                'body' => $greetingBody
            ]);
        }
        
        // Can optionally set doctor status to NOT free to talk
        // auth()->user()->update(['is_free_to_talk' => false]);

        return response()->json([
            'success' => true,
            'conversation_id' => $convo->id,
            'redirect_url' => url('/dashboard'),
            'other_user' => [
                'id' => $helpRequest->user->id,
                'name' => $helpRequest->user->short_name ?: $helpRequest->user->full_name,
                'avatar' => $helpRequest->user->avatar_url
            ]
        ]);
    }
    
    // For Doctor to Decline the Request
    public function declineRequest($id)
    {
        $helpRequest = HelpRequest::findOrFail($id);
        
        if ($helpRequest->doctor_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $helpRequest->update(['status' => 'declined']);
        
        return response()->json([
            'success' => true,
            'message' => 'Request declined'
        ]);
    }
    
    public function toggleStatus(Request $request)
    {
        $user = auth()->user();
        
        if ($request->has('is_online')) {
            $user->is_online = $request->is_online;
        }
        
        if ($request->has('is_free_to_talk')) {
            $user->is_free_to_talk = $request->is_free_to_talk;
            $user->allow_ai_recommendation = $request->is_free_to_talk;
        }
        
        $user->save();
        
        return response()->json([
            'is_online' => $user->is_online,
            'is_free_to_talk' => $user->is_free_to_talk,
            'allow_ai_recommendation' => $user->allow_ai_recommendation
        ]);
    }

    private function crisisSupportMessage(): string
    {
        return "It sounds like you may be in immediate distress. If you feel you might act on these thoughts or you are not safe right now, please call 911 in the Philippines now, go to the nearest emergency room, or ask a trusted person nearby to stay with you.\n\n" .
            "You can also contact the National Center for Mental Health (NCMH) 24/7 Crisis Hotline: 1553, 0917-899-8727, 0966-351-4518, or 1800-1888-1553.\n\n" .
            "You do not have to go through this alone, and reaching out right now is important.";
    }
}
