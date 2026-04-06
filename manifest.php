<?php
header("Content-Type: application/manifest+json; charset=utf-8");

$manifest = [
  "name" => "Advocacia System",
  "short_name" => "AdvSystem",
  "description" => "Sistema de Gerenciamento Jurídico",
  "start_url" => "/login.php",
  "scope" => "/",
  "display" => "standalone",
  "background_color" => "#ffffff",
  "theme_color" => "#0D8ABC",
  "icons" => [
    [
      "src" => "/assets/img/icon-192x192.png",
      "sizes" => "192x192",
      "type" => "image/png"
    ],
    [
      "src" => "/assets/img/icon-512x512.png",
      "sizes" => "512x512",
      "type" => "image/png"
    ]
  ]
];

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
