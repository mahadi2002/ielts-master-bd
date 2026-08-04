-- IELTS Master BD — Seed data
-- Curated general/academic IELTS-relevant vocabulary (bands 6-9) with
-- author-written definitions (not copied from any licensed dictionary).
-- Replace/expand with a properly licensed word list before launch — see
-- 01-BUILD-SPEC.md §9.4.

SET NAMES utf8mb4;

-- ============================================================
-- Free-tier words (is_exclusive = FALSE)
-- ============================================================

INSERT INTO words (id, headword, ipa, part_of_speech, definition_en, definition_bn, example_sentence, synonyms, ielts_band_level, task_tag, is_exclusive, frequency_rank) VALUES
(UUID(), 'ubiquitous', '/juːˈbɪkwɪtəs/', 'adjective', 'present, appearing, or found everywhere', 'সর্বত্র বিদ্যমান', 'Smartphones have become ubiquitous in modern life.', JSON_ARRAY('omnipresent','universal','pervasive'), 8, 'task2', FALSE, 100),
(UUID(), 'meticulous', '/mɪˈtɪkjʊləs/', 'adjective', 'showing great attention to detail; very careful and precise', 'অতি সতর্ক ও নিখুঁত', 'She kept meticulous records of every transaction.', JSON_ARRAY('careful','thorough','precise'), 7, 'task2', FALSE, 101),
(UUID(), 'unprecedented', '/ʌnˈpresɪdentɪd/', 'adjective', 'never done or known before', 'অভূতপূর্ব', 'The pandemic caused unprecedented disruption to global trade.', JSON_ARRAY('unparalleled','novel','unmatched'), 8, 'task2', FALSE, 102),
(UUID(), 'tenuous', '/ˈtenjuəs/', 'adjective', 'very weak or slight; barely sufficient', 'ক্ষীণ, দুর্বল', 'The link between the two events is tenuous at best.', JSON_ARRAY('weak','flimsy','shaky'), 8, 'task2', FALSE, 103),
(UUID(), 'exacerbate', '/ɪɡˈzæsəbeɪt/', 'verb', 'to make a problem or situation worse', 'আরও খারাপ করা', 'Poor drainage exacerbated the flooding in the city.', JSON_ARRAY('worsen','aggravate','intensify'), 8, 'task2', FALSE, 104),
(UUID(), 'ambiguous', '/æmˈbɪɡjuəs/', 'adjective', 'open to more than one interpretation; not clear', 'দ্ব্যর্থবোধক, অস্পষ্ট', 'The instructions were ambiguous and confused the students.', JSON_ARRAY('unclear','vague','equivocal'), 7, 'task2', FALSE, 105),
(UUID(), 'feasible', '/ˈfiːzəbl/', 'adjective', 'possible to do easily or conveniently', 'সম্ভবপর, কার্যকর', 'It is not economically feasible to build the bridge here.', JSON_ARRAY('viable','practicable','achievable'), 6, 'task1', FALSE, 106),
(UUID(), 'arbitrary', '/ˈɑːbɪtrəri/', 'adjective', 'based on random choice rather than reason or system', 'খামখেয়ালি, স্বেচ্ছাচারী', 'The fine seemed arbitrary and unrelated to the offence.', JSON_ARRAY('random','capricious','unsystematic'), 7, 'task2', FALSE, 107),
(UUID(), 'coherent', '/kəʊˈhɪərənt/', 'adjective', 'logical and consistent; easy to follow', 'সুসংগত, যুক্তিসঙ্গত', 'The essay lacked a coherent structure.', JSON_ARRAY('logical','consistent','clear'), 7, 'task2', FALSE, 108),
(UUID(), 'plausible', '/ˈplɔːzəbl/', 'adjective', 'seeming reasonable or probable', 'বিশ্বাসযোগ্য, যুক্তিসংগত', 'She gave a plausible explanation for the delay.', JSON_ARRAY('credible','believable','reasonable'), 7, 'task2', FALSE, 109),
(UUID(), 'pragmatic', '/præɡˈmætɪk/', 'adjective', 'dealing with things sensibly and realistically', 'বাস্তববাদী', 'The government took a pragmatic approach to the crisis.', JSON_ARRAY('practical','realistic','sensible'), 7, 'task2', FALSE, 110),
(UUID(), 'redundant', '/rɪˈdʌndənt/', 'adjective', 'not or no longer needed; superfluous', 'অপ্রয়োজনীয়, বাহুল্য', 'Several words in the sentence were redundant.', JSON_ARRAY('superfluous','unnecessary','surplus'), 7, 'general', FALSE, 111),
(UUID(), 'robust', '/rəʊˈbʌst/', 'adjective', 'strong and healthy; not easily broken or damaged', 'মজবুত, দৃঢ়', 'The economy showed robust growth despite global uncertainty.', JSON_ARRAY('sturdy','strong','resilient'), 6, 'task1', FALSE, 112),
(UUID(), 'substantial', '/səbˈstænʃl/', 'adjective', 'of considerable importance, size, or worth', 'যথেষ্ট, উল্লেখযোগ্য', 'There has been a substantial increase in tuition fees.', JSON_ARRAY('considerable','significant','sizeable'), 6, 'task1', FALSE, 113),
(UUID(), 'versatile', '/ˈvɜːsətaɪl/', 'adjective', 'able to adapt or be adapted to many functions', 'বহুমুখী, বহুমুখী প্রতিভাসম্পন্ন', 'Bamboo is a remarkably versatile building material.', JSON_ARRAY('adaptable','flexible','multi-purpose'), 6, 'general', FALSE, 114),
(UUID(), 'alleviate', '/əˈliːvieɪt/', 'verb', 'to make suffering or a problem less severe', 'লাঘব করা, উপশম করা', 'The new policy aims to alleviate poverty in rural areas.', JSON_ARRAY('ease','relieve','reduce'), 7, 'task2', FALSE, 115),
(UUID(), 'comprehensive', '/ˌkɒmprɪˈhensɪv/', 'adjective', 'complete and including everything necessary', 'ব্যাপক, পূর্ণাঙ্গ', 'The report provides a comprehensive overview of the market.', JSON_ARRAY('thorough','extensive','complete'), 6, 'task1', FALSE, 116),
(UUID(), 'controversial', '/ˌkɒntrəˈvɜːʃl/', 'adjective', 'giving rise to public disagreement', 'বিতর্কিত', 'The proposal to raise taxes proved highly controversial.', JSON_ARRAY('contentious','disputed','divisive'), 6, 'task2', FALSE, 117),
(UUID(), 'detrimental', '/ˌdetrɪˈmentl/', 'adjective', 'tending to cause harm', 'ক্ষতিকর', 'Smoking is well known to be detrimental to health.', JSON_ARRAY('harmful','damaging','adverse'), 7, 'task2', FALSE, 118),
(UUID(), 'empirical', '/ɪmˈpɪrɪkl/', 'adjective', 'based on observation or experience rather than theory', 'অভিজ্ঞতালব্ধ, প্রায়োগিক', 'The claim is not supported by empirical evidence.', JSON_ARRAY('experiential','observed','factual'), 8, 'task2', FALSE, 119),
(UUID(), 'inevitable', '/ɪnˈevɪtəbl/', 'adjective', 'certain to happen; unavoidable', 'অনিবার্য', 'Some economists see a recession as inevitable.', JSON_ARRAY('unavoidable','certain','inescapable'), 6, 'task2', FALSE, 120),
(UUID(), 'innovative', '/ˈɪnəveɪtɪv/', 'adjective', 'featuring new methods; advanced and original', 'উদ্ভাবনী', 'The company is known for its innovative products.', JSON_ARRAY('original','inventive','pioneering'), 6, 'general', FALSE, 121),
(UUID(), 'integral', '/ˈɪntɪɡrəl/', 'adjective', 'necessary to make a whole complete; essential', 'অবিচ্ছেদ্য, অপরিহার্য', 'Trust is integral to any successful relationship.', JSON_ARRAY('essential','fundamental','intrinsic'), 7, 'task2', FALSE, 122),
(UUID(), 'prevalent', '/ˈprevələnt/', 'adjective', 'widespread in a particular area or at a particular time', 'ব্যাপক, প্রচলিত', 'Obesity is increasingly prevalent among children.', JSON_ARRAY('widespread','common','rife'), 7, 'task1', FALSE, 123),
(UUID(), 'sustainable', '/səˈsteɪnəbl/', 'adjective', 'able to be maintained at a certain level without depleting resources', 'টেকসই', 'The city is investing in sustainable transport systems.', JSON_ARRAY('viable','renewable','enduring'), 6, 'task2', FALSE, 124),
(UUID(), 'viable', '/ˈvaɪəbl/', 'adjective', 'capable of working successfully; feasible', 'সম্ভাব্য, কার্যকর', 'Solar power is now a viable alternative to fossil fuels.', JSON_ARRAY('feasible','workable','practicable'), 6, 'task2', FALSE, 125),
(UUID(), 'adverse', '/ˈædvɜːs/', 'adjective', 'preventing success or development; harmful; unfavourable', 'প্রতিকূল', 'The drug can have adverse side effects.', JSON_ARRAY('unfavourable','negative','harmful'), 7, 'task1', FALSE, 126),
(UUID(), 'conducive', '/kənˈdjuːsɪv/', 'adjective', 'making a certain situation or outcome likely', 'সহায়ক, অনুকূল', 'A quiet room is conducive to studying.', JSON_ARRAY('favourable','helpful','supportive'), 7, 'task2', FALSE, 127),
(UUID(), 'discrepancy', '/dɪˈskrepənsi/', 'noun', 'a difference between things that should be the same', 'গরমিল, অসঙ্গতি', 'There is a discrepancy between the two reports.', JSON_ARRAY('inconsistency','disparity','mismatch'), 8, 'task1', FALSE, 128),
(UUID(), 'hinder', '/ˈhɪndə/', 'verb', 'to create difficulty for someone, resulting in delay or obstruction', 'বাধা দেওয়া', 'Heavy rain hindered the rescue operation.', JSON_ARRAY('obstruct','impede','hamper'), 7, 'task2', FALSE, 129),
(UUID(), 'mitigate', '/ˈmɪtɪɡeɪt/', 'verb', 'to make something less severe or serious', 'প্রশমিত করা', 'Governments must act to mitigate climate change.', JSON_ARRAY('reduce','alleviate','lessen'), 8, 'task2', FALSE, 130);

-- ============================================================
-- Exclusive reward-pool words (is_exclusive = TRUE), by band
-- ============================================================

INSERT INTO words (id, headword, ipa, part_of_speech, definition_en, definition_bn, example_sentence, synonyms, ielts_band_level, task_tag, is_exclusive, frequency_rank) VALUES
(UUID(), 'anomaly', '/əˈnɒməli/', 'noun', 'something that deviates from what is standard or expected', 'ব্যতিক্রম, অস্বাভাবিকতা', 'The data point was dismissed as a statistical anomaly.', JSON_ARRAY('irregularity','deviation','oddity'), 6, 'task1', TRUE, 200),
(UUID(), 'catalyst', '/ˈkætəlɪst/', 'noun', 'a person or thing that precipitates an event or change', 'অনুঘটক', 'The reform acted as a catalyst for economic growth.', JSON_ARRAY('trigger','spur','impetus'), 6, 'task2', TRUE, 201),
(UUID(), 'diligent', '/ˈdɪlɪdʒənt/', 'adjective', 'having or showing care in one''s work or duties', 'পরিশ্রমী, অধ্যবসায়ী', 'Diligent students review their notes every day.', JSON_ARRAY('hardworking','industrious','conscientious'), 6, 'general', TRUE, 202),
(UUID(), 'juxtapose', '/ˈdʒʌkstəpəʊz/', 'verb', 'to place things side by side for comparative effect', 'পাশাপাশি স্থাপন করা', 'The exhibit juxtaposes old photographs with modern art.', JSON_ARRAY('contrast','compare','place together'), 7, 'task1', TRUE, 203),
(UUID(), 'nuanced', '/ˈnjuːɑːnst/', 'adjective', 'characterized by subtle shades of meaning or expression', 'সূক্ষ্ম পার্থক্যযুক্ত', 'The essay offers a nuanced view of immigration policy.', JSON_ARRAY('subtle','refined','delicate'), 7, 'task2', TRUE, 204),
(UUID(), 'paradigm', '/ˈpærədaɪm/', 'noun', 'a typical example or pattern of something; a model', 'আদর্শ কাঠামো, দৃষ্টান্ত', 'The internet created a new paradigm for communication.', JSON_ARRAY('model','framework','archetype'), 7, 'task2', TRUE, 205),
(UUID(), 'quintessential', '/ˌkwɪntɪˈsenʃl/', 'adjective', 'representing the most perfect or typical example of a quality', 'সর্বসারভূত, আদর্শ প্রতীক', 'Tea is the quintessential British drink.', JSON_ARRAY('archetypal','classic','ideal'), 8, 'general', TRUE, 206),
(UUID(), 'resilience', '/rɪˈzɪliəns/', 'noun', 'the capacity to recover quickly from difficulties', 'সহনশীলতা, স্থিতিস্থাপকতা', 'The community showed great resilience after the flood.', JSON_ARRAY('toughness','durability','hardiness'), 8, 'task2', TRUE, 207),
(UUID(), 'succinct', '/səkˈsɪŋkt/', 'adjective', 'briefly and clearly expressed', 'সংক্ষিপ্ত ও স্পষ্ট', 'She gave a succinct summary of the findings.', JSON_ARRAY('concise','brief','terse'), 8, 'task1', TRUE, 208),
(UUID(), 'ephemeral', '/ɪˈfemərəl/', 'adjective', 'lasting for a very short time', 'ক্ষণস্থায়ী', 'Fame on social media is often ephemeral.', JSON_ARRAY('fleeting','transient','short-lived'), 9, 'task2', TRUE, 209),
(UUID(), 'perspicacious', '/ˌpɜːspɪˈkeɪʃəs/', 'adjective', 'having keen insight or good judgement', 'তীক্ষ্ণবুদ্ধিসম্পন্ন', 'Her perspicacious analysis impressed the entire panel.', JSON_ARRAY('perceptive','insightful','astute'), 9, 'task2', TRUE, 210),
(UUID(), 'ubiety', '/juːˈbaɪəti/', 'noun', 'the state of being in a definite place; positional existence', 'নির্দিষ্ট অবস্থান', 'Philosophers debated the ubiety of consciousness itself.', JSON_ARRAY('locality','position','whereabouts'), 9, 'general', TRUE, 211);

-- ============================================================
-- Quizzes — one MCQ per free-tier word, synonym-match style
-- ============================================================

INSERT INTO quizzes (id, word_id, quiz_type, question, options, correct_answer)
SELECT UUID(), w.id, 'mcq',
       CONCAT('"', w.headword, '" শব্দটির সবচেয়ে কাছাকাছি অর্থ কোনটি?'),
       JSON_ARRAY(JSON_UNQUOTE(JSON_EXTRACT(w.synonyms, '$[0]')), 'irrelevant', 'temporary', 'trivial'),
       JSON_UNQUOTE(JSON_EXTRACT(w.synonyms, '$[0]'))
FROM words w
WHERE w.synonyms IS NOT NULL;

INSERT INTO quizzes (id, word_id, quiz_type, question, options, correct_answer)
SELECT UUID(), w.id, 'fill_blank',
       CONCAT('Fill in the blank: "', REPLACE(w.example_sentence, w.headword, '_____'), '"'),
       NULL,
       w.headword
FROM words w
WHERE w.example_sentence IS NOT NULL AND w.example_sentence LIKE CONCAT('%', w.headword, '%');
