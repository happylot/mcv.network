import {themes as prismThemes} from 'prism-react-renderer';
import type {Config} from '@docusaurus/types';
import type * as Preset from '@docusaurus/preset-classic';

const config: Config = {
  title: 'MCV Network Docs',
  tagline: 'Product, operations, and workflow documentation for MCV Network.',
  favicon: 'img/mcv-mark.svg',

  // Future flags, see https://docusaurus.io/docs/api/docusaurus-config#future
  future: {
    v4: true, // Improve compatibility with the upcoming Docusaurus v4
  },

  // Set the production url of your site here
  url: 'https://docs.mcv.network',
  baseUrl: '/',

  organizationName: 'happylot',
  projectName: 'mcv.network',

  onBrokenLinks: 'throw',
  markdown: {
    hooks: {
      onBrokenMarkdownLinks: 'warn',
    },
  },

  // Even if you don't use internationalization, you can use this field to set
  // useful metadata like html lang. For example, if your site is Chinese, you
  // may want to replace "en" with "zh-Hans".
  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

  presets: [
    [
      'classic',
      {
        docs: {
          sidebarPath: './sidebars.ts',
          editUrl:
            'https://github.com/happylot/mcv.network/tree/main/apps/docs/',
        },
        blog: false,
        theme: {
          customCss: './src/css/custom.css',
        },
      } satisfies Preset.Options,
    ],
  ],

  themeConfig: {
    image: 'img/mcv-docs-card.svg',
    colorMode: {
      respectPrefersColorScheme: true,
    },
    navbar: {
      title: 'MCV Docs',
      logo: {
        alt: 'MCV Network',
        src: 'img/mcv-mark.svg',
      },
      items: [
        {
          type: 'docSidebar',
          sidebarId: 'productSidebar',
          position: 'left',
          label: 'Guides',
        },
        {to: '/docs/release-notes/current', label: 'Release Notes', position: 'left'},
        {href: 'https://mcv.network', label: 'Marketing', position: 'right'},
        {href: 'https://ads.mcv.network/login', label: 'Portal Login', position: 'right'},
        {
          href: 'https://github.com/happylot/mcv.network',
          label: 'GitHub',
          position: 'right',
        },
      ],
    },
    footer: {
      style: 'dark',
      links: [
        {
          title: 'Docs',
          items: [
            {label: 'Getting Started', to: '/docs/getting-started/overview'},
            {label: 'Advertiser Guide', to: '/docs/advertiser/overview'},
            {label: 'Publisher Guide', to: '/docs/publisher/overview'},
            {label: 'Admin Guide', to: '/docs/admin/overview'},
          ],
        },
        {
          title: 'Operations',
          items: [
            {label: 'Deployment', to: '/docs/operations/deployment'},
            {label: 'Roles & Access', to: '/docs/operations/roles-access'},
            {label: 'Runbook', to: '/docs/operations/runbook'},
          ],
        },
        {
          title: 'Products',
          items: [
            {label: 'mcv.network', href: 'https://mcv.network'},
            {label: 'ads.mcv.network', href: 'https://ads.mcv.network/login'},
          ],
        },
      ],
      copyright: `Copyright © ${new Date().getFullYear()} MCV Network. Built with Docusaurus.`,
    },
    prism: {
      theme: prismThemes.github,
      darkTheme: prismThemes.dracula,
    },
  } satisfies Preset.ThemeConfig,
};

export default config;
