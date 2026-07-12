<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  initialState: {
    type: Object,
    required: true
  }
});

const filter = ref('All categories');
const draftTitle = ref('');
const draftBody = ref('');
const categories = ref(props.initialState.categories || []);
const topics = ref(props.initialState.topics || []);
const stats = ref(props.initialState.stats || []);
const guidelines = ref(props.initialState.guidelines || []);

const filteredTopics = computed(() => {
  if (filter.value === 'All categories') {
    return topics.value;
  }

  return topics.value.filter((topic) => topic.category === filter.value);
});

function badgeClass(topic) {
  return {
    'forums-badge': true,
    'forums-badge-green': topic.category === 'Framework Help',
    'forums-badge-violet': topic.category === 'Announcements',
    'forums-badge-red': topic.category === 'Security'
  };
}

function createDraft() {
  const title = draftTitle.value.trim();

  if (title === '') {
    return;
  }

  topics.value.unshift({
    title,
    category: filter.value === 'All categories' ? 'Framework Help' : filter.value,
    author: 'You',
    replies: 0,
    views: 0,
    status: 'Draft',
    updated: 'Now'
  });

  draftTitle.value = '';
  draftBody.value = '';
}
</script>

<template>
  <header class="forums-topbar">
    <a class="forums-brand" href="/">
      <img :src="'/favicon.svg'" alt="WTD Core">
      <span>WTD Forums</span>
    </a>
    <nav aria-label="Forums navigation">
      <a href="/forums">Forums</a>
      <a href="/docs/api">API Docs</a>
      <a href="/health">Health</a>
    </nav>
  </header>

  <main class="forums-shell">
    <aside class="forums-sidebar">
      <section class="forums-panel forums-intro" aria-labelledby="forums-title">
        <p class="forums-eyebrow">Open source discussion</p>
        <h1 id="forums-title">Forums</h1>
        <p>Discuss framework usage, package ideas, release workflows, and implementation questions.</p>
        <a class="forums-primary" href="#new-topic">New topic</a>
      </section>

      <section class="forums-panel" aria-labelledby="forums-categories-title">
        <h2 id="forums-categories-title">Categories</h2>
        <button
          v-for="category in categories"
          :key="category.name"
          type="button"
          class="forums-category"
          :class="{ 'forums-category-active': filter === category.name }"
          @click="filter = category.name"
        >
          <span>{{ category.name }}</span>
          <strong>{{ category.count }}</strong>
        </button>
      </section>

      <section class="forums-panel forums-guidelines" aria-labelledby="forums-guidelines-title">
        <h2 id="forums-guidelines-title">Guidelines</h2>
        <ul>
          <li v-for="guideline in guidelines" :key="guideline">{{ guideline }}</li>
        </ul>
      </section>
    </aside>

    <section class="forums-content">
      <div class="forums-stats" aria-label="Forum statistics">
        <article v-for="item in stats" :key="item.label">
          <strong>{{ item.value }}</strong>
          <span>{{ item.label }}</span>
        </article>
      </div>

      <section class="forums-board" aria-label="Forum topics">
        <div class="forums-board-head">
          <div>
            <p class="forums-eyebrow">Latest activity</p>
            <h2>Community topics</h2>
          </div>
          <label>
            <span>Filter</span>
            <select v-model="filter" aria-label="Filter forum topics">
              <option>All categories</option>
              <option v-for="category in categories" :key="category.name">{{ category.name }}</option>
            </select>
          </label>
        </div>

        <div class="forums-topic-list">
          <article v-for="topic in filteredTopics" :key="topic.title" class="forums-topic">
            <div class="forums-topic-main">
              <span :class="badgeClass(topic)">{{ topic.category }}</span>
              <h3>{{ topic.title }}</h3>
              <p>{{ topic.author }} updated this topic {{ topic.updated }}.</p>
            </div>
            <dl>
              <div><dt>Replies</dt><dd>{{ topic.replies }}</dd></div>
              <div><dt>Views</dt><dd>{{ topic.views }}</dd></div>
              <div><dt>Status</dt><dd>{{ topic.status }}</dd></div>
            </dl>
          </article>
        </div>
      </section>

      <section id="new-topic" class="forums-composer" aria-labelledby="forums-composer-title">
        <div>
          <p class="forums-eyebrow">Draft topic</p>
          <h2 id="forums-composer-title">Start a useful discussion</h2>
        </div>
        <form @submit.prevent="createDraft">
          <input v-model="draftTitle" type="text" placeholder="Topic title" aria-label="Topic title">
          <textarea v-model="draftBody" placeholder="Describe the question, decision, or proposal." aria-label="Topic body"></textarea>
          <button type="submit">Create draft</button>
        </form>
      </section>
    </section>
  </main>
</template>
