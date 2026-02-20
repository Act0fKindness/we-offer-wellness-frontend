#!/usr/bin/env node
import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import url from 'node:url';
import puppeteer from 'puppeteer';

const __dirname = path.dirname(url.fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '..');

function serveStatic(root, port = 3009) {
  const server = http.createServer((req, res) => {
    try {
      let reqPath = decodeURIComponent(new URL(req.url, `http://localhost:${port}`).pathname);
      if (reqPath === '/') reqPath = '/home.static.html';
      // Map "/build" to actual build dir (symlink exists: build -> public/build)
      const filePath = path.join(root, reqPath.replace(/^\/+/, ''));
      const exists = fs.existsSync(filePath) && fs.statSync(filePath).isFile();
      if (!exists) {
        res.statusCode = 404; res.end('Not found'); return;
      }
      const ext = path.extname(filePath).slice(1);
      const type = ({ html:'text/html', js:'text/javascript', css:'text/css', json:'application/json', woff:'font/woff', woff2:'font/woff2', png:'image/png', jpg:'image/jpeg', jpeg:'image/jpeg', svg:'image/svg+xml' })[ext] || 'application/octet-stream';
      res.setHeader('Content-Type', type);
      fs.createReadStream(filePath).pipe(res);
    } catch (e) {
      res.statusCode = 500; res.end(String(e));
    }
  });
  return new Promise(resolve => server.listen(port, () => resolve(server)));
}

async function prerender() {
  const port = 3009;
  const server = await serveStatic(rootDir, port);
  const browser = await puppeteer.launch({ headless: 'new', defaultViewport: { width: 1366, height: 900 } });
  const page = await browser.newPage();
  page.on('console', msg => { /* swallow */ });
  const url = `http://localhost:${port}/home.static.html`;
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 60000 });

  // Wait for a key selector to ensure Vue rendered
  await page.waitForSelector('section.whero, .container, #app');

  const html = await page.evaluate(() => document.documentElement.outerHTML);

  // Inline main CSS bundle
  const cssPath = path.join(rootDir, 'public/build/assets/app-CUKlHKtB.css');
  const jsPath = path.join(rootDir, 'public/build/assets/app-CPq3MbIk.js');
  const css = fs.existsSync(cssPath) ? fs.readFileSync(cssPath, 'utf8') : '';
  const js = fs.existsSync(jsPath) ? fs.readFileSync(jsPath, 'utf8') : '';

  let output = html
    .replace(/<link[^>]+href=["']\/?build\/assets\/app-CUKlHKtB\.css["'][^>]*>/, () => `\n<style>${css}\n</style>`)
    .replace(/<script[^>]+src=["']\/?build\/assets\/app-CPq3MbIk\.js["'][^>]*><\/script>/, () => `\n<script type="module">${js}\n</script>`);

  const outFile = path.join(rootDir, 'index.html');
  fs.writeFileSync(outFile, output, 'utf8');

  // Also emit a static version that disables JS to preserve DOM without hydration side-effects
  let staticOutput = output
    .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
  const outStatic = path.join(rootDir, 'index.static.html');
  fs.writeFileSync(outStatic, staticOutput, 'utf8');

  // Capture an MHTML snapshot with all resources in a single file
  const client = await page.target().createCDPSession();
  const { data: mhtml } = await client.send('Page.captureSnapshot', { format: 'mhtml' });
  const outMhtml = path.join(rootDir, 'index.mhtml');
  fs.writeFileSync(outMhtml, mhtml, 'utf8');

  await browser.close();
  server.close();
  console.log('Wrote', outFile);
}

prerender().catch(err => { console.error(err); process.exit(1); });
