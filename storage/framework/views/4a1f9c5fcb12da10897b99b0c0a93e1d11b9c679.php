

<?php $__env->startSection('title', $resource->title . ' – AskDocPH'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/resources.css')); ?>?v=<?php echo e(time()); ?>">
  <style>
    .res-show-container {
        width: 100%;
        max-width: 900px;
        background: var(--panel-bg);
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-md);
        margin-bottom: 40px;
    }
    .res-show-cover {
        width: 100%;
        height: 400px;
        object-fit: cover;
    }
    .res-show-content {
        padding: 48px;
    }
    .res-show-badge {
        display: inline-block;
        padding: 6px 16px;
        background: var(--res-primary);
        color: #fff;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 24px;
    }
    .res-show-title {
        font-size: 36px;
        font-weight: 900;
        line-height: 1.2;
        margin-bottom: 24px;
        color: var(--text);
        overflow-wrap: break-word;
        word-wrap: break-word;
        word-break: break-word;
    }
    .res-show-meta {
        display: flex;
        align-items: center;
        gap: 24px;
        margin-bottom: 40px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--border);
        flex-wrap: wrap;
    }
    .res-author {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .res-author img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }
    .res-author-info {
        display: flex;
        flex-direction: column;
    }
    .res-author-name {
        font-weight: 700;
        font-size: 14px;
        color: var(--text);
    }
    .res-author-role {
        font-size: 12px;
        color: var(--muted);
    }
    .res-show-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: -16px 0 32px;
    }
    .res-show-tag {
        padding: 6px 12px;
        border-radius: 999px;
        background: var(--hover);
        border: 1px solid var(--border);
        font-size: 12px;
        font-weight: 700;
        color: var(--res-primary);
        line-height: 1;
    }
    .res-body-text {
        font-size: 18px;
        line-height: 1.8;
        color: var(--text);
        white-space: pre-wrap;
        overflow-wrap: break-word;
        word-wrap: break-word;
        word-break: break-all;
    }
    .res-actions-bar {
        position: sticky;
        bottom: 24px;
        background: #3b82f6;
        margin-top: 24px;
        padding: 16px 24px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        z-index: 10;
        color: #fff;
    }
    .share-btn-lg {
        background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
        color: #fff;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .share-btn-lg:hover {
        transform: scale(1.02);
    }
  </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<main class="dash">
    <div class="dash-body">
        <?php echo $__env->make('partials.sidebar', ['active' => 'resources'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <main class="res-main">
            <div class="res-header-panel">
                <div class="res-header-left">
                    <a href="<?php echo e(route('resources.index')); ?>" class="chip-btn" style="margin-bottom: 0;">
                        <i data-lucide="arrow-left"></i> Back to Resources
                    </a>
                </div>
                
                <?php if(Auth::check() && Auth::user()->can('update', $resource)): ?>
                <div class="res-header-right" style="display: flex; gap: 12px;">
                    <a href="<?php echo e(route('resources.edit', $resource->id)); ?>" class="chip-btn" style="background: var(--hover); border-color: var(--border);">
                        <i data-lucide="edit-3"></i> Edit
                    </a>
                    
                    <form action="<?php echo e(route('resources.destroy', $resource->id)); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this resource?');" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="chip-btn" style="color: var(--danger); border-color: var(--danger); opacity: 0.8;">
                            <i data-lucide="trash-2"></i> Delete
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <div class="res-show-container">
                <?php if($resource->thumbnail): ?>
                <img src="<?php echo e($resource->thumbnail_url); ?>" alt="<?php echo e($resource->title); ?>" class="res-show-cover">
                <?php endif; ?>

                <div class="res-show-content">
                    <span class="res-show-badge"><?php echo e($resource->type); ?></span>
                    <h1 class="res-show-title"><?php echo e($resource->title); ?></h1>
                    
                    <div class="res-show-meta">
                        <div class="res-author">
                            <img src="<?php echo e($resource->user->avatar_url); ?>" alt="<?php echo e($resource->user->full_name); ?>">
                            <div class="res-author-info">
                                <span class="res-author-name"><?php echo e($resource->user->full_name); ?></span>
                                <span class="res-author-role">Verified Expert</span>
                            </div>
                        </div>
                        <div class="res-meta-item">
                            <i data-lucide="calendar"></i>
                            <span>Published <?php echo e($resource->created_at->format('M d, Y')); ?></span>
                        </div>
                        <?php if($resource->duration_meta): ?>
                        <div class="res-meta-item">
                            <i data-lucide="clock"></i>
                            <span><?php echo e($resource->duration_meta); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if(count($resource->hashtags_array ?? [])): ?>
                      <div class="res-show-tags" aria-label="Hashtags">
                        <?php $__currentLoopData = $resource->hashtags_array; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <?php
                            $cleanTag = ltrim(trim((string) $tag), '#');
                          ?>
                          <?php if($cleanTag === '') continue; ?>
                          <span class="res-show-tag">#<?php echo e($cleanTag); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      </div>
                    <?php endif; ?>

                    <div class="res-body-content" style="display: flex; flex-direction: column; gap: 32px;">
                        
                        <?php if(in_array($resource->file_type, ['mp3', 'wav', 'ogg'])): ?>
                            <div style="padding: 24px; background: var(--hover); border-radius: 16px; border: 1px solid var(--border);">
                                <div style="font-weight: 600; color: var(--text); margin-bottom: 16px;">Audio Resource</div>
                                <audio controls style="width: 100%; border-radius: 8px;">
                                    <source src="<?php echo e($resource->file_url); ?>" type="audio/<?php echo e($resource->file_type === 'mp3' ? 'mpeg' : $resource->file_type); ?>">
                                    Your browser does not support the audio element.
                                </audio>
                                <div style="margin-top: 16px;">
                                    <a href="<?php echo e($resource->file_url); ?>" download class="chip-btn" style="background: var(--primary); color: #fff; border: none;">
                                        <i data-lucide="download"></i> Download Audio
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <?php if(in_array($resource->file_type, ['mp4', 'webm', 'mov'])): ?>
                            <div style="padding: 24px; background: var(--hover); border-radius: 16px; border: 1px solid var(--border);">
                                <div style="font-weight: 600; color: var(--text); margin-bottom: 16px;">Video Resource</div>
                                <video controls style="width: 100%; border-radius: 8px; background: #000;">
                                    <source src="<?php echo e($resource->file_url); ?>" type="video/<?php echo e($resource->file_type === 'mov' ? 'quicktime' : $resource->file_type); ?>">
                                    Your browser does not support the video element.
                                </video>
                                <div style="margin-top: 16px;">
                                    <a href="<?php echo e($resource->file_url); ?>" download class="chip-btn" style="background: var(--primary); color: #fff; border: none;">
                                        <i data-lucide="download"></i> Download Video
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <?php
                            $fileType = $resource->file_type;
                            $fileUrl = $resource->file_url;
                        ?>

                        
                        <?php if($fileUrl && in_array($fileType, ['doc', 'docx', 'xls', 'xlsx'])): ?>
                            <div style="padding: 24px; background: var(--hover); border-radius: 16px; border: 1px solid var(--border);">
                                <div style="font-weight: 600; color: var(--text); margin-bottom: 12px;">Download Document</div>
                                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                    <a href="<?php echo e($fileUrl); ?>" download class="chip-btn" style="background: var(--primary); color: #fff; border: none;">
                                        <i data-lucide="download"></i> Download <?php echo e(strtoupper($fileType)); ?>

                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <?php if($fileUrl && $fileType === 'xml'): ?>
                            <div style="display: flex; align-items: center; gap: 16px; padding: 24px; background: var(--hover); border-radius: 16px; border: 1px solid var(--border);">
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: var(--text); margin-bottom: 4px;">XML File</div>
                                    <div style="font-size: 14px; color: var(--muted);"><?php echo e($resource->title); ?>.xml</div>
                                </div>
                                <div style="display: flex; gap: 12px;">
                                    <a href="<?php echo e($fileUrl); ?>" target="_blank" rel="noopener noreferrer" class="chip-btn" style="background: var(--primary); color: #fff; border: none;">
                                        <i data-lucide="external-link"></i> View XML
                                    </a>
                                    <a href="<?php echo e($fileUrl); ?>" download class="chip-btn" style="background: var(--hover); color: var(--text); border: 1px solid var(--border);">
                                        <i data-lucide="download"></i> Download
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <?php if(in_array($resource->file_type, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                            <img src="<?php echo e($resource->file_url); ?>" style="width: 100%; border-radius: 16px; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                        <?php endif; ?>

                        <?php
                            $safeContent = $resource->content ?: $resource->description;
                            // Multi-pass cleanup for any persistent blob URLs
                            $safeContent = preg_replace('/<(video|audio|source|img)\s+[^>]*src="blob:[^"]+"[^>]*>.*?<\/\1>/is', '', $safeContent);
                            $safeContent = preg_replace('/<(video|audio|source|img)\s+[^>]*src="blob:[^"]+"[^>]*>/is', '', $safeContent);

                            // Convert placeholder links (about:blank / javascript:void(0)) into real URLs.
                            // If a placeholder already has a `data-href`, prefer it (e.g. attached docs uploaded via editor).
                            if ($resource->file_url) {
                                $safeContent = preg_replace_callback(
                                    '/<a([^>]*?)href="(?:about:blank|javascript:void\(0\)|#)"([^>]*?)>(.*?)<\/a>/is',
                                    function ($matches) use ($resource) {
                                        $attrs = $matches[1] . $matches[2];
                                        $href = null;

                                        if (preg_match('/data-href="([^"]+)"/i', $attrs, $m)) {
                                            $href = $m[1];
                                            if (preg_match('/^(?:about:blank|javascript:void\\(0\\)|#)$/i', $href)) {
                                                $href = null;
                                            }
                                        } elseif (preg_match('/data-ext="([^"]+)"/i', $attrs, $m)) {
                                            $ext = strtolower($m[1]);
                                            if ($ext === $resource->file_type) {
                                                $href = $resource->file_url;
                                            }
                                        }

                                        if (!$href && $resource->file_url) {
                                            $text = $matches[3] ?? '';
                                            if (preg_match('/\.pdf\b/i', $text) && $resource->file_type === 'pdf') {
                                                $href = $resource->file_url;
                                            } elseif (preg_match('/\.docx?\b/i', $text) && in_array($resource->file_type, ['doc', 'docx'])) {
                                                $href = $resource->file_url;
                                            } elseif (preg_match('/\.xlsx?\b/i', $text) && in_array($resource->file_type, ['xls', 'xlsx'])) {
                                                $href = $resource->file_url;
                                            } elseif (preg_match('/\.xml\b/i', $text) && $resource->file_type === 'xml') {
                                                $href = $resource->file_url;
                                            }
                                        }

                                        if ($href) {
                                            return '<a' . $matches[1] . 'href="' . $href . '"' . $matches[2] . '>' . $matches[3] . '</a>';
                                        }

                                        return $matches[0];
                                    },
                                    $safeContent
                                );

                                // Also handle explicitly marked placeholders (class-based) if present.
                                libxml_use_internal_errors(true);
                                $dom = new \DOMDocument('1.0', 'UTF-8');
                                $dom->loadHTML('<?xml version="1.0" encoding="UTF-8" ?><div>' . $safeContent . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                                $xpath = new \DOMXPath($dom);
                                $links = $xpath->query('//a[contains(concat(" ", normalize-space(@class), " "), " file-link-placeholder ")]');
                                foreach ($links as $link) {
                                    $href = $link->getAttribute('data-href');
                                    if ($href && preg_match('/^(?:about:blank|javascript:void\\(0\\)|#)$/i', $href)) {
                                        $href = '';
                                    }
                                    $ext = strtolower($link->getAttribute('data-ext') ?? '');
                                    // If the placeholder matches the resource's primary file and no data-href exists, fall back to the primary file URL.
                                    if (!$href && $resource->file_url && $resource->file_type === $ext) {
                                        $href = $resource->file_url;
                                    }
                                    if ($href) {
                                        $link->setAttribute('href', $href);
                                        $link->setAttribute('target', '_blank');
                                        $link->setAttribute('rel', 'noopener noreferrer');
                                    }
                                }
                                $container = $dom->getElementsByTagName('div')->item(0);
                                $safeContent = '';
                                if ($container) {
                                    foreach ($container->childNodes as $child) {
                                        $safeContent .= $dom->saveHTML($child);
                                    }
                                }
                            }

                            // Cleanup empty paragraphs left behind
                            $safeContent = preg_replace('/<p>\s*<\/p>/i', '', $safeContent);
                        ?>
                        <div class="res-body-text"><?php echo $safeContent; ?></div>
                    </div>

                <div class="res-actions-bar">
                    <div style="font-size: 14px; font-weight: 600;">
                        Found this helpful? Join it and share with your community.
                    </div>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <?php if(auth()->guard()->check()): ?>
                          <?php if($isJoined ?? false): ?>
                            <form method="POST" action="<?php echo e(route('resources.unjoin', $resource->id)); ?>">
                              <?php echo csrf_field(); ?>
                              <?php echo method_field('DELETE'); ?>
                              <button type="submit" class="share-btn-lg" style="background:#10b981;">
                                <i data-lucide="check"></i> Joined
                              </button>
                            </form>
                          <?php else: ?>
                            <form method="POST" action="<?php echo e(route('resources.join', $resource->id)); ?>">
                              <?php echo csrf_field(); ?>
                              <button type="submit" class="share-btn-lg" style="background:#22c55e;">
                                <i data-lucide="user-plus"></i> Join Resource
                              </button>
                            </form>
                          <?php endif; ?>
                        <?php endif; ?>

                        <button class="share-btn-lg js-share-resource" type="button" data-resource-id="<?php echo e($resource->id); ?>" data-preview="<?php echo e($resource->title); ?>">
                            <i data-lucide="share-2"></i> Share to Feed
                        </button>
                    </div>
                </div>
                </div>
            </div>
        </main>
    </div>
</main>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileUrl = "<?php echo e($resource->file_url); ?>";

    document.querySelectorAll('.file-link-placeholder').forEach(link => {
        const href = link.dataset.href || link.getAttribute('href') || '';
        const ext = (link.dataset.ext || '').toLowerCase();
        const officeExts = ['doc','docx','xls','xlsx'];

        // Prefer `data-href` if present; else keep whatever href is set.
        let finalHref = href;
        if (!href && fileUrl && ext === "<?php echo e($resource->file_type); ?>") {
            finalHref = fileUrl;
        }

        // Office files can be previewed in Office Web Viewer, but we will still force-download on click.
        if (officeExts.includes(ext) && finalHref && !finalHref.startsWith('blob:')) {
            finalHref = `https://view.officeapps.live.com/op/view.aspx?src=${encodeURIComponent(finalHref)}`;
        }

        if (finalHref) {
            // For non-PDF downloads, embed the download params in the URL so right-click/save works.
            if (!['pdf'].includes(ext) && finalHref) {
                const safeFileName = (link.dataset.fileName || (link.textContent || '').trim()).replace(/[\\/\\0\n\r]/g, '_');
                try {
                    const url = new URL(finalHref, window.location.href);
                    url.searchParams.set('dl', '1');
                    url.searchParams.set('fn', safeFileName);
                    finalHref = url.toString();
                } catch (err) {
                    // If URL parsing fails, keep the original finalHref.
                }
            }

            link.href = finalHref;
            link.target = "_blank";
            link.rel = "noopener noreferrer";
        }

        // Force download for non-PDF types.
        link.addEventListener('click', function(e) {
            const clickExt = (link.dataset.ext || '').toLowerCase();
            if (clickExt === 'pdf') {
                return; // allow normal open
            }

            let downloadUrl = link.dataset.href || link.href;
            if (!downloadUrl || /^(javascript:void\(0\)|#)$/.test(downloadUrl)) {
                return;
            }

            // If this is an Office viewer URL, try to extract the underlying file URL.
            const officeViewerPrefix = 'https://view.officeapps.live.com/op/view.aspx?src=';
            if (downloadUrl.startsWith(officeViewerPrefix)) {
                try {
                    const url = new URL(downloadUrl);
                    const src = url.searchParams.get('src');
                    if (src) downloadUrl = decodeURIComponent(src);
                } catch (err) {
                    // ignore
                }
            }

            const fileName = link.dataset.fileName || (link.textContent || '').trim();

            e.preventDefault();
            const safeFileName = fileName.replace(/[\\/\\0\n\r]/g, '_');
            const url = new URL(downloadUrl, window.location.href);
            url.searchParams.set('dl', '1');
            url.searchParams.set('fn', safeFileName);

            const a = document.createElement('a');
            a.href = url.toString();
            a.download = safeFileName;
            document.body.appendChild(a);
            a.click();
            a.remove();
        });
    });
});
</script>

<div id="dash-toast" class="dash-toast" style="position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); z-index: 1000;"></div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/resources/show.blade.php ENDPATH**/ ?>