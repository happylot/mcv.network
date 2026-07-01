import Link from '@docusaurus/Link';
import Layout from '@theme/Layout';
import type {ReactNode} from 'react';

const guides = [
  {
    title: 'Advertiser Guide',
    description: 'Top up wallet, browse approved publisher websites, and buy guest post placements.',
    to: '/docs/advertiser/overview',
  },
  {
    title: 'Publisher Guide',
    description: 'Submit websites, receive guest post orders, publish content, and submit fulfillment URLs.',
    to: '/docs/publisher/overview',
  },
  {
    title: 'Admin Guide',
    description: 'Approve publisher websites, review submitted orders, and release payouts when needed.',
    to: '/docs/admin/overview',
  },
  {
    title: 'Operations',
    description: 'Deployment notes, role management, production checks, and runbooks.',
    to: '/docs/operations/deployment',
  },
];

export default function Home(): ReactNode {
  return (
    <Layout
      title="MCV Network Docs"
      description="Product and operations documentation for MCV Network">
      <header className="hero hero--primary">
        <div className="container">
          <h1 className="hero__title">MCV Network Docs</h1>
          <p className="hero__subtitle">
            Living documentation for the advertiser portal, publisher marketplace, admin workflows, and deployment operations.
          </p>
          <div>
            <Link className="button button--secondary button--lg" to="/docs/getting-started/overview">
              Open Guides
            </Link>
          </div>
        </div>
      </header>
      <main className="container margin-vert--lg">
        <section className="mcv-home-grid">
          {guides.map((guide) => (
            <Link className="mcv-card" to={guide.to} key={guide.title}>
              <h3>{guide.title}</h3>
              <p>{guide.description}</p>
            </Link>
          ))}
        </section>
      </main>
    </Layout>
  );
}
