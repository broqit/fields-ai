<?php

return [
    'default_model' => 'gpt-4o',
    'default_max_tokens' => 150,
    'default_temperature' => 0.7,

    'content_templates' => [
        'product_description' => 'Write a compelling product description for a [product name].',
        'blog_intro' => 'Write an engaging introduction for a blog post about [topic].',
        'email_subject' => 'Create an attention-grabbing email subject line for [purpose].',
        'social_media_post' => 'Craft a social media post promoting [event/product].',
        'seo_meta_description' => 'Write an SEO-friendly meta description for a webpage about [topic].',
        'customer_service_reply' => 'Compose a polite customer service reply addressing [issue].',
        'faq_answer' => 'Provide a clear and concise answer to the FAQ: [question].',
        'press_release_headline' => 'Create a newsworthy headline for a press release about [news item].',
        'video_script_intro' => 'Write an engaging introduction for a video script about [topic].',
        'podcast_episode_summary' => 'Summarize the key points of a podcast episode about [topic].',
    ],

    'content_actions' => [
        'refine' => 'Refine the following text: %s',
        'expand' => 'Expand on the following text by adding more details, examples, or explanations. Ensure that your response is a continuation of the existing content and forms complete sentences and paragraphs: %s',
        'shorten' => 'Shorten the following text while maintaining its key points: %s',
        'translate' => 'Rewrite and translate the following text to Ukrainian with save html structure and original images: %s',
    ],
];
