<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OCR / AI Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "tesseract" (free/local), "openai_vision", "google_vision", "aws_textract"
    | Set the driver and corresponding API credentials in .env.
    |
    */

    'provider' => env('INVOICE_OCR_PROVIDER', 'tesseract'),

    /*
    |--------------------------------------------------------------------------
    | Auto-Delete Uploaded Images After Extraction
    |--------------------------------------------------------------------------
    |
    | When true, uploaded invoice images are deleted from storage after
    | extraction. Set to false to retain for audit or reprocessing.
    |
    */

    'auto_delete_image_after_extraction' => env('INVOICE_OCR_AUTO_DELETE_IMAGE', true),

    /*
    |--------------------------------------------------------------------------
    | Tesseract OCR – Free Local OCR (when provider = tesseract)
    |--------------------------------------------------------------------------
    |
    | Uses Tesseract OCR installed on your system. No API key needed!
    | Install: https://github.com/UB-Mannheim/tesseract/wiki (Windows)
    |          sudo apt install tesseract-ocr (Linux)
    |          brew install tesseract (Mac)
    |
    */

    'tesseract' => [
        'executable' => env('TESSERACT_PATH', ''),  // Leave empty to use system PATH, or set full path
        'lang'       => env('TESSERACT_LANG', 'eng'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Cloud Vision (when provider = google_vision)
    |--------------------------------------------------------------------------
    */

    'google_vision' => [
        'credentials' => env('GOOGLE_VISION_CREDENTIALS'), // path to JSON key file
        'project_id'  => env('GOOGLE_CLOUD_PROJECT_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS Textract (when provider = aws_textract)
    |--------------------------------------------------------------------------
    */

    'aws_textract' => [
        'key'    => env('AWS_TEXTRACT_ACCESS_KEY_ID'),
        'secret' => env('AWS_TEXTRACT_SECRET_ACCESS_KEY'),
        'region' => env('AWS_TEXTRACT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI Vision (when provider = openai_vision)
    |--------------------------------------------------------------------------
    */

    'openai_vision' => [
        'api_key' => env('OPENAI_API_KEY', ''),
        'model'   => env('OPENAI_VISION_MODEL', 'gpt-4o'),
    ],

];
