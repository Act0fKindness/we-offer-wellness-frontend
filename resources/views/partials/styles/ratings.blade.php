@once
  @push('head')
    <style>
      .wow-review-row{
        display:flex;
        align-items:center;
        gap:12px;
        margin-top:14px;
        font-size:13px;
        font-weight:600;
        color:#0b1220;
      }
      .wow-content-top .wow-review-row{ margin-top:auto; padding-top:12px; }
      .wow-review-stars{
        display:inline-flex;
        align-items:center;
        gap:4px;
        flex:0 0 auto;
      }
      .wow-review-star{
        width:18px;
        height:18px;
        position:relative;
        display:inline-block;
        background:#e2e8f0;
        -webkit-mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
        mask:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z'/%3E%3C/svg%3E") center/contain no-repeat;
      }
      .wow-review-star::after{
        content:"";
        position:absolute;
        inset:0;
        background:#f5c84b;
        opacity:0;
        pointer-events:none;
      }
      .wow-review-star--full::after{ opacity:1; }
      .wow-review-star--half::after{
        opacity:1;
        width:50%;
        right:auto;
      }
      .wow-review-meta{
        display:flex;
        align-items:baseline;
        gap:6px;
        flex-wrap:wrap;
      }
      .wow-review-score{
        font-size:15px;
        font-weight:700;
        color:#0e1527;
      }
      .wow-review-count{
        font-weight:600;
        font-size:13px;
        color:#5f6b7b;
      }
      .wow-row-card .wow-review-row,
      .wow-therapy-card-scope .wow-review-row{ justify-content:flex-start; }
      .wow-card-sm .wow-review-row{
        margin-top:12px;
        font-size:12px;
      }
      .wow-card-sm .wow-review-star{ width:15px; height:15px; }
      @media (max-width: 576px){
        .wow-review-row{ gap:10px; }
      }
    </style>
  @endpush
@endonce
