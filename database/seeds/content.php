<?php
declare(strict_types=1);

/**
 * Word catalog + guide library seed data.
 *
 * The word list is a small, author-written starter set for development —
 * see docs/FEATURES.md for the plan to replace it with a properly licensed
 * list before launch. Definitions here are written from scratch, not lifted
 * from any dictionary API or book.
 */

use App\Core\Db;

$words = [
    // headword, ipa, pos, def_en, def_bn, example, synonyms, band, task_tag, exclusive, freq
    ['ubiquitous', '/juːˈbɪkwɪtəs/', 'adjective', 'present, appearing, or found everywhere', 'সর্বত্র বিদ্যমান', 'Smartphones have become ubiquitous in modern life.', ['omnipresent', 'universal', 'pervasive'], 8, 'task2', false, 100],
    ['meticulous', '/mɪˈtɪkjʊləs/', 'adjective', 'showing great attention to detail; very careful and precise', 'অতি সতর্ক ও নিখুঁত', 'She kept meticulous records of every transaction.', ['careful', 'thorough', 'precise'], 7, 'task2', false, 101],
    ['unprecedented', '/ʌnˈpresɪdentɪd/', 'adjective', 'never done or known before', 'অভূতপূর্ব', 'The pandemic caused unprecedented disruption to global trade.', ['unparalleled', 'novel', 'unmatched'], 8, 'task2', false, 102],
    ['tenuous', '/ˈtenjuəs/', 'adjective', 'very weak or slight; barely sufficient', 'ক্ষীণ, দুর্বল', 'The link between the two events is tenuous at best.', ['weak', 'flimsy', 'shaky'], 8, 'task2', false, 103],
    ['exacerbate', '/ɪɡˈzæsəbeɪt/', 'verb', 'to make a problem or situation worse', 'আরও খারাপ করা', 'Poor drainage exacerbated the flooding in the city.', ['worsen', 'aggravate', 'intensify'], 8, 'task2', false, 104],
    ['ambiguous', '/æmˈbɪɡjuəs/', 'adjective', 'open to more than one interpretation; not clear', 'দ্ব্যর্থবোধক, অস্পষ্ট', 'The instructions were ambiguous and confused the students.', ['unclear', 'vague', 'equivocal'], 7, 'task2', false, 105],
    ['feasible', '/ˈfiːzəbl/', 'adjective', 'possible to do easily or conveniently', 'সম্ভবপর, কার্যকর', 'It is not economically feasible to build the bridge here.', ['viable', 'practicable', 'achievable'], 6, 'task1', false, 106],
    ['arbitrary', '/ˈɑːbɪtrəri/', 'adjective', 'based on random choice rather than reason or system', 'খামখেয়ালি, স্বেচ্ছাচারী', 'The fine seemed arbitrary and unrelated to the offence.', ['random', 'capricious', 'unsystematic'], 7, 'task2', false, 107],
    ['coherent', '/kəʊˈhɪərənt/', 'adjective', 'logical and consistent; easy to follow', 'সুসংগত, যুক্তিসঙ্গত', 'The essay lacked a coherent structure.', ['logical', 'consistent', 'clear'], 7, 'task2', false, 108],
    ['plausible', '/ˈplɔːzəbl/', 'adjective', 'seeming reasonable or probable', 'বিশ্বাসযোগ্য, যুক্তিসংগত', 'She gave a plausible explanation for the delay.', ['credible', 'believable', 'reasonable'], 7, 'task2', false, 109],
    ['pragmatic', '/præɡˈmætɪk/', 'adjective', 'dealing with things sensibly and realistically', 'বাস্তববাদী', 'The government took a pragmatic approach to the crisis.', ['practical', 'realistic', 'sensible'], 7, 'task2', false, 110],
    ['redundant', '/rɪˈdʌndənt/', 'adjective', 'not or no longer needed; superfluous', 'অপ্রয়োজনীয়, বাহুল্য', 'Several words in the sentence were redundant.', ['superfluous', 'unnecessary', 'surplus'], 7, 'general', false, 111],
    ['robust', '/rəʊˈbʌst/', 'adjective', 'strong and healthy; not easily broken or damaged', 'মজবুত, দৃঢ়', 'The economy showed robust growth despite global uncertainty.', ['sturdy', 'strong', 'resilient'], 6, 'task1', false, 112],
    ['substantial', '/səbˈstænʃl/', 'adjective', 'of considerable importance, size, or worth', 'যথেষ্ট, উল্লেখযোগ্য', 'There has been a substantial increase in tuition fees.', ['considerable', 'significant', 'sizeable'], 6, 'task1', false, 113],
    ['versatile', '/ˈvɜːsətaɪl/', 'adjective', 'able to adapt or be adapted to many functions', 'বহুমুখী, বহুমুখী প্রতিভাসম্পন্ন', 'Bamboo is a remarkably versatile building material.', ['adaptable', 'flexible', 'multi-purpose'], 6, 'general', false, 114],
    ['alleviate', '/əˈliːvieɪt/', 'verb', 'to make suffering or a problem less severe', 'লাঘব করা, উপশম করা', 'The new policy aims to alleviate poverty in rural areas.', ['ease', 'relieve', 'reduce'], 7, 'task2', false, 115],
    ['comprehensive', '/ˌkɒmprɪˈhensɪv/', 'adjective', 'complete and including everything necessary', 'ব্যাপক, পূর্ণাঙ্গ', 'The report provides a comprehensive overview of the market.', ['thorough', 'extensive', 'complete'], 6, 'task1', false, 116],
    ['controversial', '/ˌkɒntrəˈvɜːʃl/', 'adjective', 'giving rise to public disagreement', 'বিতর্কিত', 'The proposal to raise taxes proved highly controversial.', ['contentious', 'disputed', 'divisive'], 6, 'task2', false, 117],
    ['detrimental', '/ˌdetrɪˈmentl/', 'adjective', 'tending to cause harm', 'ক্ষতিকর', 'Smoking is well known to be detrimental to health.', ['harmful', 'damaging', 'adverse'], 7, 'task2', false, 118],
    ['empirical', '/ɪmˈpɪrɪkl/', 'adjective', 'based on observation or experience rather than theory', 'অভিজ্ঞতালব্ধ, প্রায়োগিক', 'The claim is not supported by empirical evidence.', ['experiential', 'observed', 'factual'], 8, 'task2', false, 119],
    ['inevitable', '/ɪnˈevɪtəbl/', 'adjective', 'certain to happen; unavoidable', 'অনিবার্য', 'Some economists see a recession as inevitable.', ['unavoidable', 'certain', 'inescapable'], 6, 'task2', false, 120],
    ['innovative', '/ˈɪnəveɪtɪv/', 'adjective', 'featuring new methods; advanced and original', 'উদ্ভাবনী', 'The company is known for its innovative products.', ['original', 'inventive', 'pioneering'], 6, 'general', false, 121],
    ['integral', '/ˈɪntɪɡrəl/', 'adjective', 'necessary to make a whole complete; essential', 'অবিচ্ছেদ্য, অপরিহার্য', 'Trust is integral to any successful relationship.', ['essential', 'fundamental', 'intrinsic'], 7, 'task2', false, 122],
    ['prevalent', '/ˈprevələnt/', 'adjective', 'widespread in a particular area or at a particular time', 'ব্যাপক, প্রচলিত', 'Obesity is increasingly prevalent among children.', ['widespread', 'common', 'rife'], 7, 'task1', false, 123],
    ['sustainable', '/səˈsteɪnəbl/', 'adjective', 'able to be maintained at a certain level without depleting resources', 'টেকসই', 'The city is investing in sustainable transport systems.', ['viable', 'renewable', 'enduring'], 6, 'task2', false, 124],
    ['viable', '/ˈvaɪəbl/', 'adjective', 'capable of working successfully; feasible', 'সম্ভাব্য, কার্যকর', 'Solar power is now a viable alternative to fossil fuels.', ['feasible', 'workable', 'practicable'], 6, 'task2', false, 125],
    ['adverse', '/ˈædvɜːs/', 'adjective', 'preventing success or development; harmful; unfavourable', 'প্রতিকূল', 'The drug can have adverse side effects.', ['unfavourable', 'negative', 'harmful'], 7, 'task1', false, 126],
    ['conducive', '/kənˈdjuːsɪv/', 'adjective', 'making a certain situation or outcome likely', 'সহায়ক, অনুকূল', 'A quiet room is conducive to studying.', ['favourable', 'helpful', 'supportive'], 7, 'task2', false, 127],
    ['discrepancy', '/dɪˈskrepənsi/', 'noun', 'a difference between things that should be the same', 'গরমিল, অসঙ্গতি', 'There is a discrepancy between the two reports.', ['inconsistency', 'disparity', 'mismatch'], 8, 'task1', false, 128],
    ['hinder', '/ˈhɪndə/', 'verb', 'to create difficulty for someone, resulting in delay or obstruction', 'বাধা দেওয়া', 'Heavy rain hindered the rescue operation.', ['obstruct', 'impede', 'hamper'], 7, 'task2', false, 129],
    ['mitigate', '/ˈmɪtɪɡeɪt/', 'verb', 'to make something less severe or serious', 'প্রশমিত করা', 'Governments must act to mitigate climate change.', ['reduce', 'alleviate', 'lessen'], 8, 'task2', false, 130],
    // ── Exclusive reward-pool words, by band ──
    ['anomaly', '/əˈnɒməli/', 'noun', 'something that deviates from what is standard or expected', 'ব্যতিক্রম, অস্বাভাবিকতা', 'The data point was dismissed as a statistical anomaly.', ['irregularity', 'deviation', 'oddity'], 6, 'task1', true, 200],
    ['catalyst', '/ˈkætəlɪst/', 'noun', 'a person or thing that precipitates an event or change', 'অনুঘটক', 'The reform acted as a catalyst for economic growth.', ['trigger', 'spur', 'impetus'], 6, 'task2', true, 201],
    ['diligent', '/ˈdɪlɪdʒənt/', 'adjective', "having or showing care in one's work or duties", 'পরিশ্রমী, অধ্যবসায়ী', 'Diligent students review their notes every day.', ['hardworking', 'industrious', 'conscientious'], 6, 'general', true, 202],
    ['juxtapose', '/ˈdʒʌkstəpəʊz/', 'verb', 'to place things side by side for comparative effect', 'পাশাপাশি স্থাপন করা', 'The exhibit juxtaposes old photographs with modern art.', ['contrast', 'compare', 'place together'], 7, 'task1', true, 203],
    ['nuanced', '/ˈnjuːɑːnst/', 'adjective', 'characterized by subtle shades of meaning or expression', 'সূক্ষ্ম পার্থক্যযুক্ত', 'The essay offers a nuanced view of immigration policy.', ['subtle', 'refined', 'delicate'], 7, 'task2', true, 204],
    ['paradigm', '/ˈpærədaɪm/', 'noun', 'a typical example or pattern of something; a model', 'আদর্শ কাঠামো, দৃষ্টান্ত', 'The internet created a new paradigm for communication.', ['model', 'framework', 'archetype'], 7, 'task2', true, 205],
    ['quintessential', '/ˌkwɪntɪˈsenʃl/', 'adjective', 'representing the most perfect or typical example of a quality', 'সর্বসারভূত, আদর্শ প্রতীক', 'Tea is the quintessential British drink.', ['archetypal', 'classic', 'ideal'], 8, 'general', true, 206],
    ['resilience', '/rɪˈzɪliəns/', 'noun', 'the capacity to recover quickly from difficulties', 'সহনশীলতা, স্থিতিস্থাপকতা', 'The community showed great resilience after the flood.', ['toughness', 'durability', 'hardiness'], 8, 'task2', true, 207],
    ['succinct', '/səkˈsɪŋkt/', 'adjective', 'briefly and clearly expressed', 'সংক্ষিপ্ত ও স্পষ্ট', 'She gave a succinct summary of the findings.', ['concise', 'brief', 'terse'], 8, 'task1', true, 208],
    ['ephemeral', '/ɪˈfemərəl/', 'adjective', 'lasting for a very short time', 'ক্ষণস্থায়ী', 'Fame on social media is often ephemeral.', ['fleeting', 'transient', 'short-lived'], 9, 'task2', true, 209],
    ['perspicacious', '/ˌpɜːspɪˈkeɪʃəs/', 'adjective', 'having keen insight or good judgement', 'তীক্ষ্ণবুদ্ধিসম্পন্ন', 'Her perspicacious analysis impressed the entire panel.', ['perceptive', 'insightful', 'astute'], 9, 'task2', true, 210],
    ['inscrutable', '/ɪnˈskruːtəbl/', 'adjective', 'impossible to understand or interpret', 'দুর্বোধ্য, রহস্যময়', 'His inscrutable expression gave nothing away.', ['enigmatic', 'unfathomable', 'mysterious'], 9, 'general', true, 211],
];

$slugCounts = [];
$wordIds = [];

foreach ($words as [$headword, $ipa, $pos, $defEn, $defBn, $example, $synonyms, $band, $taskTag, $exclusive, $freq]) {
    $slug = slugify($headword);
    if (isset($slugCounts[$slug])) {
        $slugCounts[$slug]++;
        $slug .= '-' . $slugCounts[$slug];
    } else {
        $slugCounts[$slug] = 1;
    }

    $id = Db::insert(
        'INSERT INTO words (slug, headword, ipa, part_of_speech, definition_en, definition_bn,
            example_sentence, synonyms, ielts_band_level, task_tag, is_exclusive, frequency_rank, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
        [
            $slug, $headword, $ipa, $pos, $defEn, $defBn, $example,
            json_encode($synonyms, JSON_UNESCAPED_UNICODE),
            $band, $taskTag, $exclusive ? 1 : 0, $freq,
        ]
    );
    $wordIds[$headword] = $id;

    // One MCQ (synonym match) + one fill-in-the-blank per word.
    Db::insert(
        'INSERT INTO quizzes (word_id, quiz_type, question, options, correct_answer) VALUES (?, "mcq", ?, ?, ?)',
        [
            $id,
            '"' . $headword . '" শব্দটির সবচেয়ে কাছাকাছি অর্থ কোনটি?',
            json_encode([$synonyms[0], 'irrelevant', 'temporary', 'trivial'], JSON_UNESCAPED_UNICODE),
            $synonyms[0],
        ]
    );

    if (str_contains($example, $headword)) {
        Db::insert(
            'INSERT INTO quizzes (word_id, quiz_type, question, correct_answer) VALUES (?, "fill_blank", ?, ?)',
            [$id, 'Fill in the blank: "' . str_replace($headword, '_____', $example) . '"', $headword]
        );
    }
}

// ── Guide library ─────────────────────────────────────────────────────────
// Author-written strategy notes — generic exam technique, not exam-leaked
// content, not copied from any prep book.
$guides = [
    [
        'Writing Task 2: the four-paragraph structure that actually scores',
        'writing_task2',
        'একটা predictable structure মেনে চললে Task 2-তে Task Response আর Coherence দুটোতেই স্কোর বাড়ে।',
        "IELTS Writing Task 2 rewards a predictable structure far more than a clever one — the examiner is scoring Task Response and Coherence & Cohesion against a checklist, not judging your essay for originality.\n\n## The four paragraphs\n\n- **Introduction (2-3 sentences):** paraphrase the question, then state your position clearly if it's an opinion essay.\n- **Body paragraph 1:** one main idea, one topic sentence, one concrete example. Do not merge two ideas into one paragraph — that is the single most common Coherence penalty.\n- **Body paragraph 2:** a second, distinct idea, same shape as the first.\n- **Conclusion (1-2 sentences):** restate your position in different words. Never introduce a new idea here.\n\n## Why this works\n\nA clear structure lets the examiner find your Task Response points quickly, which is exactly what the band descriptors reward. Save your best vocabulary for varying *how* you say things inside this structure, not for breaking the structure itself.",
        '6-9',
    ],
    [
        'Speaking Part 2: surviving the one-minute prep',
        'speaking',
        'এক মিনিটের প্রস্তুতিতে পুরো গল্প লিখতে যাবেন না — শুধু একটা কাঠামো তৈরি করুন।',
        "You get one minute to prepare a two-minute talk on a cue card. Trying to write full sentences in that minute is the most common reason candidates freeze halfway through.\n\n## What to do instead\n\n- Read the cue card's four bullet points as your outline — they already are one.\n- Jot down 4-6 single words or short phrases, one per bullet, not sentences.\n- Pick a real or invented specific detail (a name, a place, a rough date) for each point — specific detail is what separates Band 6 from Band 7+ here, not vocabulary.\n\n## While speaking\n\nIf you run out of things to say before two minutes, add a sentence about how you *felt* about the topic — feelings and opinions extend naturally and the examiner will not cut you off early just because you've technically covered the bullet points.",
        '6-9',
    ],
    [
        'Reading: why you should answer True/False/Not Given last',
        'reading',
        'True/False/Not Given প্রশ্নগুলো সবচেয়ে বেশি সময় নেয় — তাই এগুলো সবার শেষে answer করুন।',
        "True/False/Not Given (and Yes/No/Not Given) questions take longer per question than almost any other IELTS Reading question type, because 'Not Given' requires you to be certain the information genuinely is not in the passage — not just that you didn't find it.\n\n## A better order\n\n1. Answer every other question type in the section first — matching headings, multiple choice, sentence completion.\n2. Come back to True/False/Not Given last, when you already understand the passage's overall structure from doing the other questions.\n3. For each statement, find the exact sentence in the passage that addresses it. If you can't find one after a genuine search, it is Not Given — do not guess True or False out of frustration.\n\nThis ordering alone recovers several minutes for most candidates, which is often the difference between finishing the section and running out of time.",
        '6-9',
    ],
    [
        'Listening: the trap of the "corrected answer"',
        'listening',
        'স্পিকার প্রথমে একটা উত্তর বলে পরে নিজেই ঠিক করে — এটাই সবচেয়ে সাধারণ ফাঁদ।',
        "The single most common IELTS Listening trap is the self-correction: a speaker says one answer, then corrects themselves a few seconds later — \"the meeting's on Tuesday... actually, sorry, we moved it to Wednesday.\"\n\n## Why it catches people out\n\nCandidates who write down the first answer they hear and move on lose marks here constantly, because the recording only plays once and there's no way to go back.\n\n## What to listen for\n\n- Words that signal a correction: *actually, sorry, I mean, no wait, let me correct that*.\n- Write your first answer in pencil-lightly (mentally or literally) and stay alert until the speaker has clearly moved to the next point — a correction can come a full sentence later.\n- If two numbers or dates are mentioned close together, the second one is very often the actual answer.",
        '6-9',
    ],
    [
        'Building topic-specific vocabulary instead of random word lists',
        'vocabulary',
        'র্যান্ডম শব্দ মুখস্থ না করে, IELTS-এ বারবার আসা Topic ধরে শব্দ শিখুন — মনে রাখা সহজ হয়।',
        "Random vocabulary lists are hard to remember because the words have no relationship to each other. IELTS essay and speaking topics repeat across a fairly small set of themes — environment, education, technology, health, urbanisation, crime — and words cluster naturally within each one.\n\n## A better approach\n\n- When you learn a new word, ask which topic it belongs to (this app tags every word by its most common Task usage for exactly this reason).\n- Learn 3-4 words from the same topic together, in one sitting — they reinforce each other because you can immediately imagine using them in the same sentence.\n- Prioritise words that work as both nouns and other forms (*sustainable → sustainability*, *innovate → innovative → innovation*) — one root, several usable forms, less to memorise overall.\n\nBand 7+ vocabulary is really about precision within a topic, not sheer word count.",
        '6-9',
    ],
    [
        'Why "big words" can lower your Writing score',
        'writing_task2',
        'সবচেয়ে কঠিন শব্দটা ব্যবহার করাই সবসময় ভালো স্ট্র্যাটেজি না — ভুল প্রয়োগ Lexical Resource স্কোর কমিয়ে দেয়।',
        "A common mistake is reaching for the most impressive-sounding word available, on the theory that harder vocabulary always scores higher. It does not — the Lexical Resource band descriptor explicitly penalises *unnatural* or *inaccurate* word choice, even when the word itself is advanced.\n\n## What actually gets rewarded\n\n- Using a Band 7 word correctly beats using a Band 9 word incorrectly, every time.\n- If you are not sure a word fits the exact context, use a simpler word you are certain about instead.\n- Collocations matter as much as individual words: *make a decision*, not *do a decision*. Learning correct collocations is often more valuable than learning more standalone words.\n\nThe safest way to raise Lexical Resource is a small set of advanced words you have used correctly many times before the exam, not a new word you tried for the first time in the test itself.",
        '6-9',
    ],
];

foreach ($guides as [$title, $category, $excerpt, $bodyMd, $bandRelevance]) {
    $slug = slugify($title);
    Db::insert(
        'INSERT INTO guides (slug, title, category, excerpt, body_md, band_relevance, is_published, published_at, created_at)
         VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())',
        [$slug, $title, $category, $excerpt, $bodyMd, $bandRelevance]
    );
}

out('  ' . count($words) . ' words, ' . count($guides) . ' guides seeded.');
