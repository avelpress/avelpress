import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "AvelPress",
  description: "Laravel-inspired PHP Framework for WordPress Plugins & Themes",
  base: '/',

  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    logo: '/assets/avelpress.png',

    nav: [
      { text: 'Home', link: '/' },
      { text: 'Guide', link: '/guide/getting-started' },
      { text: 'API Reference', link: '/api/overview' },
      { text: 'Examples', link: '/examples/basic-plugin' },
      { text: 'FAQ', link: '/faq' }
    ],

    sidebar: {
      '/guide/': [
        {
          text: 'Getting Started',
          items: [
            { text: 'Introduction', link: '/guide/introduction' },
            { text: 'Installation', link: '/guide/installation' },
            { text: 'Getting Started', link: '/guide/getting-started' },
          ]
        },
        {
          text: 'Core Concepts',
          items: [
            { text: 'Application Structure', link: '/guide/application-structure' },
            { text: 'Service Providers', link: '/guide/service-providers' },
            { text: 'Dependency Injection', link: '/guide/dependency-injection' },
            { text: 'Facades', link: '/guide/facades' }
          ]
        },
        {
          text: 'Routing',
          items: [
            { text: 'Basic Routing', link: '/guide/routing/basic' },
            { text: 'Route Parameters', link: '/guide/routing/parameters' },
            { text: 'Route Groups', link: '/guide/routing/groups' },
            { text: 'Controllers', link: '/guide/routing/controllers' }
          ]
        },
        {
          text: 'Database',
          items: [
            { text: 'Getting Started', link: '/guide/database/getting-started' },
            { text: 'Migrations', link: '/guide/database/migrations' },
            { text: 'Schema Builder', link: '/guide/database/schema' },
            { text: 'Eloquent Models', link: '/guide/database/eloquent' },
            { text: 'Relationships', link: '/guide/database/relationships' },
            { text: 'Collections', link: '/guide/database/collections' }
          ]
        },
        {
          text: 'HTTP',
          items: [
            { text: 'JSON Resources', link: '/guide/http/json-resources' },
            { text: 'Resource Collections', link: '/guide/http/resource-collections' },
            { text: 'Validation', link: '/guide/http/validation' }
          ]
        }
      ],
      '/api/': [
        {
          text: 'API Reference',
          items: [
            { text: 'Overview', link: '/api/overview' },
            { text: 'Application', link: '/api/application' },
            { text: 'Router', link: '/api/router' },
            { text: 'Model', link: '/api/model' },
            { text: 'Schema', link: '/api/schema' },
            { text: 'Facades', link: '/api/facades' }
          ]
        }
      ],
      '/examples/': [
        {
          text: 'Examples',
          items: [
            { text: 'Basic Plugin', link: '/examples/basic-plugin' },
            { text: 'REST API Plugin', link: '/examples/rest-api-plugin' },
            { text: 'Custom Post Type', link: '/examples/custom-post-type' },
            { text: 'Admin Dashboard', link: '/examples/admin-dashboard' }
          ]
        }
      ]
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/your-username/avelpress' }
    ],

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2024-present AvelPress'
    },

    search: {
      provider: 'local'
    }
  }
})
