import { createApp } from 'vue';
import '../../css/modules/forums.css';

const stateElement = document.getElementById('forums-initial-state');
const mountElement = document.querySelector('[data-forums-app]');
const initialState = stateElement ? JSON.parse(stateElement.textContent || '{}') : {};

const ForumsApp = {
  data() {
    return {
      filter: 'All categories',
      draftTitle: '',
      draftBody: '',
      categories: initialState.categories || [],
      topics: initialState.topics || [],
      stats: initialState.stats || [],
      guidelines: initialState.guidelines || []
    };
  },
  computed: {
    filteredTopics() {
      if (this.filter === 'All categories') {
        return this.topics;
      }

      return this.topics.filter((topic) => topic.category === this.filter);
    }
  },
  methods: {
    badgeClass(topic) {
      return {
        'forums-badge': true,
        'forums-badge-green': topic.category === 'Framework Help',
        'forums-badge-violet': topic.category === 'Announcements',
        'forums-badge-red': topic.category === 'Security'
      };
    },
    createDraft() {
      if (!this.draftTitle.trim()) {
        return;
      }

      this.topics.unshift({
        title: this.draftTitle.trim(),
        category: this.filter === 'All categories' ? 'Framework Help' : this.filter,
        author: 'You',
        replies: 0,
        views: 0,
        status: 'Draft',
        updated: 'Now'
      });
      this.draftTitle = '';
      this.draftBody = '';
    }
  },
  template: `
    <header class="forums-topbar">
      <a class="forums-brand" href="/">
        <img src="/favicon.svg" alt="WTD Core">
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
        <div class="forums-panel">
          <p class="forums-eyebrow">Open source discussion</p>
          <h1>Forums</h1>
          <p>Discuss framework usage, package ideas, release workflows, and implementation questions.</p>
          <a class="forums-primary" href="#new-topic">New topic</a>
        </div>
        <div class="forums-panel">
          <h2>Categories</h2>
          <button
            v-for="category in categories"
            :key="category.name"
            type="button"
            class="forums-category"
            @click="filter = category.name"
          >
            <span>{{ category.name }}</span>
            <strong>{{ category.count }}</strong>
          </button>
        </div>
        <div class="forums-panel forums-guidelines">
          <h2>Guidelines</h2>
          <ul>
            <li v-for="guideline in guidelines" :key="guideline">{{ guideline }}</li>
          </ul>
        </div>
      </aside>

      <section class="forums-content">
        <div class="forums-stats">
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
              <div>
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

        <section id="new-topic" class="forums-composer">
          <div>
            <p class="forums-eyebrow">Draft topic</p>
            <h2>Start a useful discussion</h2>
          </div>
          <form @submit.prevent="createDraft">
            <input v-model="draftTitle" type="text" placeholder="Topic title" aria-label="Topic title">
            <textarea v-model="draftBody" placeholder="Describe the question, decision, or proposal." aria-label="Topic body"></textarea>
            <button type="submit">Create draft</button>
          </form>
        </section>
      </section>
    </main>
  `
};

if (mountElement) {
  createApp(ForumsApp).mount(mountElement);
}

