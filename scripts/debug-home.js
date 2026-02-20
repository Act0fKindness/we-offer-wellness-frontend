#!/usr/bin/env node
import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import puppeteer from 'puppeteer';

const root = process.cwd();

function serve(port=3020){
  const srv = http.createServer((req,res)=>{
    const u = new URL(req.url, `http://localhost:${port}`);
    let p = u.pathname;
    if (p === '/' || p === '/index.html') p = '/home.full.html';
    const fp = path.join(root, p.replace(/^\//,''));
    if (!fs.existsSync(fp)) { res.statusCode=404; res.end('404'); return; }
    const ext = path.extname(fp).slice(1);
    const ct = ({html:'text/html', js:'text/javascript', css:'text/css', woff2:'font/woff2', woff:'font/woff'})[ext]||'application/octet-stream';
    res.setHeader('Content-Type', ct);
    fs.createReadStream(fp).pipe(res);
  });
  return new Promise(r=>srv.listen(port,()=>r(srv)));
}

(async()=>{
  const port=3020;
  const srv=await serve(port);
  const browser=await puppeteer.launch({headless:'new'});
  const page=await browser.newPage();
  page.on('console', msg => console.log('[console]', msg.type(), msg.text()));
  page.on('pageerror', err => console.log('[pageerror]', err.message));
  try {
    await page.goto(`http://localhost:${port}/`, {waitUntil:'networkidle0', timeout:60000});
    // Check if Inertia mounted (look for app vue comment or body content size)
    const mounted = await page.evaluate(()=>{
      const h = document.querySelector('header');
      const hasBurger = !!document.querySelector('button[aria-label="Toggle menu"]');
      return { hasBurger, bodyLen: document.body.innerText.length };
    });
    console.log('mounted:', mounted);
  } catch(e){
    console.log('error navigating:', e.message);
  }
  await browser.close();
  srv.close();
})();

