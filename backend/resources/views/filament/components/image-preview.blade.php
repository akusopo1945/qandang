<div class="flex justify-center items-center p-4">
    <img 
        src="{{ $imageUrl }}" 
        alt="Preview" 
        width="1200"
        height="1200"
        loading="lazy"
        decoding="async"
        class="max-w-full h-auto rounded-lg shadow-lg cursor-zoom-in transition-transform duration-300 hover:scale-105"
        onclick="window.open(this.src, '_blank')"
    >
</div>
