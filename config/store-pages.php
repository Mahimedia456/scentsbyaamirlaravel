<?php

return [
    'contact' => [
        'eyebrow' => 'Customer Care',
        'title' => 'Contact & Support',
        'intro' => 'Thoughtful assistance before, during and after your fragrance purchase.',
        'theme' => 'light',
        'sections' => [
            [
                'title' => 'Order support',
                'copy' => 'Questions about an order, delivery or product can be handled through the support form below. Live customer-service integration will connect during backend development.',
            ],
            [
                'title' => 'Product guidance',
                'copy' => 'For help choosing a fragrance, use the Fragrance Finder or contact the house with the mood, materials and occasion you prefer.',
            ],
        ],
    ],

    'shipping' => [
        'eyebrow' => 'Customer Care',
        'title' => 'Shipping',
        'intro' => 'A clear delivery experience designed around careful fragrance handling.',
        'theme' => 'light',
        'sections' => [
            ['title' => 'Standard delivery', 'copy' => 'Estimated delivery windows and live rates will be supplied by the backend and courier integration.'],
            ['title' => 'Express delivery', 'copy' => 'Express delivery may be offered where supported by the selected destination and fulfilment rules.'],
            ['title' => 'Order tracking', 'copy' => 'Once backend order tracking is connected, tracking details will appear inside My Account and on the Track Order page.'],
        ],
    ],

    'returns' => [
        'eyebrow' => 'Customer Care',
        'title' => 'Returns',
        'intro' => 'A structured returns experience with product-condition and order-validation checks.',
        'theme' => 'light',
        'sections' => [
            ['title' => 'Return eligibility', 'copy' => 'Final return windows, hygiene rules and accepted product conditions will be configured from the backend policy settings.'],
            ['title' => 'Damaged delivery', 'copy' => 'If an item arrives damaged, the support workflow can collect order details and supporting images once backend integration is enabled.'],
            ['title' => 'Refund processing', 'copy' => 'Approved refunds will follow the original payment method and gateway rules after live payment integration.'],
        ],
    ],

    'gift-wrapping' => [
        'eyebrow' => 'House Services',
        'title' => 'Gift Wrapping',
        'intro' => 'A restrained, premium presentation for fragrance gifts.',
        'theme' => 'dark',
        'sections' => [
            ['title' => 'House presentation', 'copy' => 'Selected orders can be prepared in a dedicated gift presentation with fragrance-safe packaging.'],
            ['title' => 'Add at checkout', 'copy' => 'Gift wrapping will be selectable during checkout once service availability is connected to the backend.'],
        ],
    ],

    'personalized-message' => [
        'eyebrow' => 'House Services',
        'title' => 'Personalized Message',
        'intro' => 'Add a private note to make the fragrance experience more personal.',
        'theme' => 'dark',
        'sections' => [
            ['title' => 'Your words', 'copy' => 'A short personalized message can accompany eligible gifts.'],
            ['title' => 'Checkout integration', 'copy' => 'The message field will be stored with the order after backend order creation is connected.'],
        ],
    ],

    'privacy' => [
        'eyebrow' => 'Legal',
        'title' => 'Privacy Policy',
        'intro' => 'How customer information will be handled across the Scents by Aamir storefront.',
        'theme' => 'light',
        'sections' => [
            ['title' => 'Information we collect', 'copy' => 'Account, order, delivery and support information will only be collected where needed to operate the store and provide requested services.'],
            ['title' => 'How information is used', 'copy' => 'Customer information may be used for fulfilment, support, account management, fraud prevention and consent-based communication.'],
            ['title' => 'Data control', 'copy' => 'Backend implementation will include the final retention, deletion and account-data workflows required for production.'],
        ],
    ],

    'terms' => [
        'eyebrow' => 'Legal',
        'title' => 'Terms & Conditions',
        'intro' => 'The commercial terms governing use of the storefront and purchase of fragrance products.',
        'theme' => 'light',
        'sections' => [
            ['title' => 'Orders', 'copy' => 'Orders will become final only after live inventory, pricing and payment checks are completed by the production backend.'],
            ['title' => 'Pricing', 'copy' => 'Displayed pricing is frontend data during the current build and will later come from the database.'],
            ['title' => 'Service availability', 'copy' => 'Delivery, gifting and payment options may vary by destination and configured business rules.'],
        ],
    ],

    'cookies' => [
        'eyebrow' => 'Legal',
        'title' => 'Cookie Policy',
        'intro' => 'A clear overview of browser storage and future production cookies.',
        'theme' => 'light',
        'sections' => [
            ['title' => 'Essential storage', 'copy' => 'The current frontend uses local browser storage for cart and wishlist persistence.'],
            ['title' => 'Analytics and preferences', 'copy' => 'Production analytics or preference cookies will only be introduced when the relevant services and consent controls are configured.'],
            ['title' => 'Your choices', 'copy' => 'A production cookie-preference interface can be connected during backend and compliance integration.'],
        ],
    ],

    'accessibility' => [
        'eyebrow' => 'Legal',
        'title' => 'Accessibility',
        'intro' => 'The storefront is designed to remain usable across keyboard, touch and reduced-motion experiences.',
        'theme' => 'light',
        'sections' => [
            ['title' => 'Keyboard access', 'copy' => 'Interactive controls include visible focus states and the layout provides a skip-to-content link.'],
            ['title' => 'Reduced motion', 'copy' => 'Animation is reduced or disabled when the visitor requests reduced motion.'],
            ['title' => 'Ongoing review', 'copy' => 'Accessibility remains part of final QA as real content, payment flows and backend features are connected.'],
        ],
    ],
];
