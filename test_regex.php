<?php
$content = '<p><video controls style="max-width:100%;border-radius:12px;margin:8px 0;" src="blob:http://127.0.0.1:8000/1a455e5f"></video></p>';
$safeContent = preg_replace('/<video [^>]*src="blob:[^>]+><\/video>/i', '', $content);
$safeContent = preg_replace('/<p><video [^>]*src="blob:[^>]+><\/video><\/p>/i', '', $content);
echo "Result:\n" . $safeContent . "\n";
