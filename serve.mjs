/* Minimal static file server for previewing the MCV site.
   Run: node serve.mjs   ->   http://localhost:8080
   Serves /foo/ as /foo/index.html. */
import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { join, extname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { dirname } from 'node:path';

const ROOT = dirname(fileURLToPath(import.meta.url));
const PORT = process.env.PORT || 8080;
const TYPES = { '.html': 'text/html', '.css': 'text/css', '.js': 'text/javascript', '.png': 'image/png', '.jpg': 'image/jpeg', '.svg': 'image/svg+xml', '.json': 'application/json', '.ico': 'image/x-icon' };

createServer(async (req, res) => {
  try {
    let p = decodeURIComponent(req.url.split('?')[0]);
    if (p.endsWith('/')) p += 'index.html';
    let file = join(ROOT, p);
    try { const s = await stat(file); if (s.isDirectory()) file = join(file, 'index.html'); } catch {}
    const data = await readFile(file);
    res.writeHead(200, { 'Content-Type': TYPES[extname(file)] || 'application/octet-stream' });
    res.end(data);
  } catch {
    res.writeHead(404, { 'Content-Type': 'text/html' });
    res.end('<h1>404</h1>');
  }
}).listen(PORT, () => console.log(`MCV preview at http://localhost:${PORT}`));
