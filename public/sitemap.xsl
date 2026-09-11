<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9"
  exclude-result-prefixes="s">
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>
  <xsl:strip-space elements="*"/>

  <xsl:template match="/">
    <html lang="en">
      <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>We Offer Wellness Sitemap</title>
        <style>
          :root {
            color-scheme: light;
            --bg: #f4f7f4;
            --panel: #ffffff;
            --panel-soft: #f7faf8;
            --text: #12312b;
            --muted: #5f766f;
            --accent: #3b7768;
            --accent-2: #f4b860;
            --border: rgba(18, 49, 43, 0.12);
            --shadow: 0 24px 60px rgba(17, 32, 28, 0.10);
          }
          * { box-sizing: border-box; }
          body {
            margin: 0;
            font-family: Inter, Manrope, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
              radial-gradient(circle at top left, rgba(59, 119, 104, 0.14), transparent 32%),
              radial-gradient(circle at top right, rgba(244, 184, 96, 0.14), transparent 28%),
              var(--bg);
            color: var(--text);
          }
          .wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 36px 20px 56px;
          }
          .hero {
            display: grid;
            gap: 16px;
            padding: 28px;
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(255,255,255,.98), rgba(247,250,248,.96));
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
          }
          .eyebrow {
            text-transform: uppercase;
            letter-spacing: .24em;
            font-size: 12px;
            color: var(--accent);
            font-weight: 700;
          }
          h1 {
            margin: 0;
            font-size: clamp(28px, 4vw, 48px);
            line-height: .98;
          }
          .summary {
            max-width: 72ch;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
            margin: 0;
          }
          .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 4px;
          }
          .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(59, 119, 104, 0.08);
            color: var(--accent);
            font-size: 13px;
            font-weight: 700;
          }
          .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
            overflow: hidden;
          }
          .table-wrap {
            overflow-x: auto;
          }
          table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
          }
          thead th {
            text-align: left;
            font-size: 12px;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 18px 20px;
            background: linear-gradient(180deg, #fbfcfb, #f4f8f6);
            border-bottom: 1px solid var(--border);
          }
          tbody td {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(18, 49, 43, 0.08);
            vertical-align: top;
            font-size: 14px;
          }
          tbody tr:nth-child(even) td {
            background: var(--panel-soft);
          }
          a {
            color: var(--accent);
            text-decoration: none;
            word-break: break-word;
          }
          a:hover {
            text-decoration: underline;
          }
          .loc {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: 12px;
          }
          .count {
            display: inline-flex;
            min-width: 40px;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(244, 184, 96, 0.16);
            color: #7a5200;
            font-weight: 700;
          }
          .footer {
            margin-top: 18px;
            color: var(--muted);
            font-size: 13px;
          }
          @media (max-width: 720px) {
            .wrap { padding: 18px 12px 36px; }
            .hero { padding: 20px; border-radius: 22px; }
            table { min-width: 640px; }
          }
        </style>
      </head>
      <body>
        <div class="wrap">
          <div class="hero">
            <div class="eyebrow">We Offer Wellness</div>
            <h1>Sitemap Index</h1>
            <p class="summary">
              A browsable index of the site’s XML sitemap files. The table below shows each sitemap file, its last update, and how many URLs it contains.
            </p>
            <div class="meta">
              <span class="pill">XML sitemap</span>
              <span class="pill">AI-friendly browsing</span>
              <span class="pill">Canonical URLs</span>
            </div>
          </div>

          <div class="card">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>File</th>
                    <th>Last Updated</th>
                    <th>URLs</th>
                  </tr>
                </thead>
                <tbody>
                  <xsl:choose>
                    <xsl:when test="/s:sitemapindex">
                      <xsl:for-each select="/s:sitemapindex/s:sitemap">
                        <tr>
                          <td>
                            <a href="{s:loc}">
                              <xsl:value-of select="s:loc"/>
                            </a>
                          </td>
                          <td>
                            <xsl:value-of select="s:lastmod"/>
                          </td>
                          <td><span class="count">1</span></td>
                        </tr>
                      </xsl:for-each>
                    </xsl:when>
                    <xsl:otherwise>
                      <xsl:for-each select="/s:urlset/s:url">
                        <tr>
                          <td>
                            <a href="{s:loc}">
                              <xsl:value-of select="s:loc"/>
                            </a>
                          </td>
                          <td>
                            <xsl:value-of select="s:lastmod"/>
                          </td>
                          <td><span class="count">1</span></td>
                        </tr>
                      </xsl:for-each>
                    </xsl:otherwise>
                  </xsl:choose>
                </tbody>
              </table>
            </div>
          </div>

          <p class="footer">Generated for We Offer Wellness. The stylesheet makes XML sitemap files human-readable without affecting crawler access.</p>
        </div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>