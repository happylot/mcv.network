import type {SidebarsConfig} from '@docusaurus/plugin-content-docs';

// This runs in Node.js - Don't use client-side code here (browser APIs, JSX...)

/**
 * Creating a sidebar enables you to:
 - create an ordered group of docs
 - render a sidebar for each doc of that group
 - provide next/previous navigation

 The sidebars can be generated from the filesystem, or explicitly defined here.

 Create as many sidebars as you want.
 */
const sidebars: SidebarsConfig = {
  productSidebar: [
    {
      type: 'category',
      label: 'Getting Started',
      items: ['getting-started/overview', 'getting-started/platform-map'],
    },
    {
      type: 'category',
      label: 'Advertiser Guide',
      items: ['advertiser/overview', 'advertiser/top-up-wallet', 'advertiser/buy-guest-post'],
    },
    {
      type: 'category',
      label: 'Publisher Guide',
      items: ['publisher/overview', 'publisher/submit-website', 'publisher/fulfill-order'],
    },
    {
      type: 'category',
      label: 'Admin Guide',
      items: ['admin/overview', 'admin/approve-websites', 'admin/approve-orders'],
    },
    {
      type: 'category',
      label: 'Billing & Wallet',
      items: ['billing/stripe-top-up', 'billing/wallet-ledger'],
    },
    {
      type: 'category',
      label: 'Marketplace',
      items: ['marketplace/lifecycle', 'marketplace/statuses'],
    },
    {
      type: 'category',
      label: 'Operations',
      items: ['operations/deployment', 'operations/roles-access', 'operations/runbook'],
    },
    {
      type: 'category',
      label: 'Release Notes',
      items: ['release-notes/current'],
    },
  ],
};

export default sidebars;
