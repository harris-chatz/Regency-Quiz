<script setup lang="ts">
import { RouterView } from 'vue-router'
</script>

<template>
  <div class="min-h-dvh flex flex-col">
    <header class="px-6 py-4 flex items-center justify-between border-b border-white/5">
      <div class="flex items-center gap-3">
        <img
          src="/logo.png"
          alt="Regency Casino"
          class="h-8 md:h-10 w-auto select-none"
          draggable="false"
        />
        <span class="hidden sm:inline text-xs uppercase tracking-widest text-white/50 border-l border-white/15 pl-3">
          Mont Parnes
        </span>
      </div>
      <span class="text-xs uppercase tracking-widest text-white/40">Quiz Game</span>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-8">
      <RouterView v-slot="{ Component }">
        <Transition name="fade" mode="out-in">
          <component :is="Component" />
        </Transition>
      </RouterView>
    </main>

    <footer class="px-6 py-3 text-center text-xs text-white/30 border-t border-white/5">
      © {{ new Date().getFullYear() }} Regency Casino Mont Parnes · Παίξε υπεύθυνα · 21+
    </footer>
  </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
