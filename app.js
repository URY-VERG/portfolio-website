const data = window.PORTFOLIO_DATA;

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

function setText(selector, value) {
  const node = $(selector);
  if (node) node.textContent = value;
}

function setLink(selector, value) {
  const node = $(selector);
  if (node && value) node.href = value;
}

function createTag(text) {
  const tag = document.createElement("span");
  tag.className = "tag";
  tag.textContent = text;
  return tag;
}

function renderStats() {
  const container = $("[data-stats]");
  container.replaceChildren(
    ...data.stats.map((item) => {
      const stat = document.createElement("article");
      stat.className = "stat-item";
      stat.innerHTML = `<strong>${item.value}</strong><span>${item.label}</span>`;
      return stat;
    })
  );
}

function renderProjects() {
  const container = $("[data-projects]");
  container.replaceChildren(
    ...data.projects.map((project) => {
      const card = document.createElement("article");
      card.className = "project-card";

      const tech = document.createElement("div");
      tech.className = "tag-row";
      tech.append(...project.tech.map(createTag));

      card.innerHTML = `
        <img src="${project.image}" alt="${project.title} preview" />
        <div class="project-body">
          <p class="project-type">${project.type}</p>
          <h3>${project.title}</h3>
          <p>${project.description}</p>
          <div class="project-links">
            <a href="${project.liveUrl}" aria-label="Open ${project.title} live demo">Live</a>
            <a href="${project.codeUrl}" aria-label="Open ${project.title} code">Code</a>
          </div>
        </div>
      `;
      $(".project-body", card).insertBefore(tech, $(".project-links", card));
      return card;
    })
  );
}

function renderLearning() {
  const container = $("[data-learning]");
  container.replaceChildren(
    ...data.learning.map((item) => {
      const block = document.createElement("article");
      block.className = "timeline-item";
      block.innerHTML = `
        <span>${item.period}</span>
        <h3>${item.title}</h3>
        <p>${item.body}</p>
      `;
      return block;
    })
  );
}

function renderSkills() {
  const container = $("[data-skills]");
  container.replaceChildren(...data.skills.map(createTag));
}

function renderCertificates() {
  const container = $("[data-certificates]");
  container.replaceChildren(
    ...data.certificates.map((certificate) => {
      const card = document.createElement("article");
      card.className = "certificate-card";
      card.innerHTML = `
        <a href="${certificate.url}" aria-label="Open ${certificate.title}">
          <img src="${certificate.image}" alt="${certificate.title}" />
        </a>
        <div>
          <h3>${certificate.title}</h3>
          <p>${certificate.issuer}</p>
          <span>${certificate.date}</span>
        </div>
      `;
      return card;
    })
  );
}

function renderAccounts() {
  const container = $("[data-accounts]");
  container.replaceChildren(
    ...data.accounts.map((account) => {
      const row = document.createElement("a");
      row.className = "account-row";
      row.href = account.url;
      row.innerHTML = `
        <span>${account.label}</span>
        <strong>${account.handle}</strong>
      `;
      return row;
    })
  );
}

function initNavigation() {
  const button = $("[data-menu-button]");
  const nav = $("[data-nav]");

  button.addEventListener("click", () => {
    const isOpen = nav.classList.toggle("is-open");
    button.setAttribute("aria-label", isOpen ? "Close navigation" : "Open navigation");
  });

  $$("[data-nav] a").forEach((link) => {
    link.addEventListener("click", () => nav.classList.remove("is-open"));
  });
}

function hydrateProfile() {
  setText("[data-initials]", data.initials);
  setText("[data-name]", data.name);
  setText("[data-role]", data.role);
  setText("[data-location]", data.location);
  setText("[data-headline]", data.headline);
  setText("[data-summary]", data.summary);
  setText("[data-availability]", data.availability);
  setText("[data-footer-name]", data.name);
  setText("[data-year]", new Date().getFullYear());
  setLink("[data-resume]", data.resumeUrl);
  setLink("[data-email-link]", `mailto:${data.email}`);
  setLink("[data-phone-link]", `tel:${data.phone.replace(/\s/g, "")}`);
  $("[data-photo]").src = data.photo;
  document.title = `${data.name} Portfolio`;
}

hydrateProfile();
renderStats();
renderProjects();
renderLearning();
renderSkills();
renderCertificates();
renderAccounts();
initNavigation();
