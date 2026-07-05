"use client";

const rawHtml = `
<!-- Pega aquí TODO el contenido que ahora mismo está dentro de <body> en tu index.html -->
<div class="text-gray-200 min-h-screen flex flex-col selection:bg-blue-600 selection:text-white">
  <!-- Ejemplo: reemplazar por tu HTML real -->
  <main class="flex-1">
    <section class="max-w-6xl mx-auto py-8">
      <h1 class="text-2xl font-bold mb-4">RetailMind Dashboard POC</h1>
      <p>Este es un placeholder. Aquí debe ir tu HTML completo del dashboard.</p>
    </section>
  </main>
</div>
<!-- FIN del contenido -->
`;

export default function Page() {
  return (
    <div
      dangerouslySetInnerHTML={{ __html: rawHtml }}
    />
  );
}
