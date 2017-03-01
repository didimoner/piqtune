<header class="header">
	<span class="title">PiqTune - Редактор Изображений <span style="font-size: 12px">[Автор: Fengyuan Chen]</span></span>
	<button-box></button-box>
</header>
<main class="main">
	<upload-box></upload-box>
	<canvas-box></canvas-box>
</main>

<script id="button-box" type="text/x-template">
	<div @click="click" class="menu">
		<label for="file" title="Загрузить" v-show="!uploaded" class="menu__button"><span
				class="fa fa-upload"></span></label>
		<button data-action="restore" title="Отмена (Ctrl + Z)" v-show="cropped" class="menu__button"><span
				class="fa fa-undo"></span></button>
		<button data-action="remove" title="Удалить (Delete)" v-show="uploaded &amp;&amp; !cropping"
		        class="menu__button menu__button--danger"><span class="fa fa-trash"></span></button>
		<button data-action="clear" title="Отменить (Esc)" v-show="cropping" class="menu__button menu__button--danger">
			<span class="fa fa-ban"></span></button>
		<button data-action="crop" title="OK (Enter)" v-show="cropping" class="menu__button menu__button--success"><span
				class="fa fa-check"></span></button>
		<a data-action="download" href="{{ url }}" title="Скачать" download="{{ name }}" v-show="url"
		   class="menu__button menu__button--success"><span class="fa fa-download"></span></a>
		<a data-action="download" href="https://github.com/fengyuanchen/photo-editor" title="Автор редактора на GitHub"
		   class="menu__button" target="_blank"><span class="fa fa-github"></span></a>
	</div>
</script>
<script id="upload-box" type="text/x-template">
	<div @change="change" @dragover="dragover" @drop="drop" v-show="!uploaded" class="upload">
		<p>Перетащите изображение сюда или <label class="browse">выберете на компьютере...<input id="file" type="file"
		                                                                                         accept="image/*"
		                                                                                         class="sr-only"></label>
		</p>
	</div>
</script>
<script id="canvas-box" type="text/x-template">
	<div v-show="editable" class="canvas">
		<div @dblclick="dblclick" class="editor">
			<template v-if="url"><img src="{{ url }}" alt="{{ name }}" @load="load"></template>
		</div>
		<div @click="click" v-show="cropper" class="toolbar">
			<button data-action="move" title="Переместить (M)" class="toolbar__button"><span
					class="fa fa-arrows"></span></button>
			<button data-action="crop" title="Обрезать (C)" class="toolbar__button"><span class="fa fa-crop"></span>
			</button>
			<button data-action="zoom-in" title="Увеличить (I)" class="toolbar__button"><span
					class="fa fa-search-plus"></span></button>
			<button data-action="zoom-out" title="Уменьшить (O)" class="toolbar__button"><span
					class="fa fa-search-minus"></span></button>
			<button data-action="rotate-left" title="Повернуть влево (L)" class="toolbar__button"><span
					class="fa fa-rotate-left"></span></button>
			<button data-action="rotate-right" title="Повернуть вправо (R)" class="toolbar__button"><span
					class="fa fa-rotate-right"></span></button>
			<button data-action="flip-horizontal" title="Отразить по горизонтали (H)" class="toolbar__button"><span
					class="fa fa-arrows-h"></span></button>
			<button data-action="flip-vertical" title="Отразить по вертикали (V)" class="toolbar__button"><span
					class="fa fa-arrows-v"></span></button>
		</div>
	</div>
</script>