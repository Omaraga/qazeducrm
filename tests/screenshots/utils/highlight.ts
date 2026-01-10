import { Page } from '@playwright/test';

export interface HighlightOptions {
  color?: string;
  width?: number;
  padding?: number;
  label?: string | number;
  labelPosition?: 'top' | 'bottom' | 'left' | 'right';
  arrow?: boolean;
  arrowDirection?: 'top' | 'bottom' | 'left' | 'right';
}

/**
 * Добавляет визуальное выделение элемента на странице
 */
export async function highlightElement(
  page: Page,
  selector: string,
  options: HighlightOptions = {}
): Promise<void> {
  const defaults: HighlightOptions = {
    color: '#FE8D00',
    width: 3,
    padding: 4,
    label: null,
    labelPosition: 'top',
    arrow: false,
    arrowDirection: 'left',
  };

  const config = { ...defaults, ...options };

  await page.evaluate(({ selector, config }) => {
    const el = document.querySelector(selector);
    if (!el) {
      console.warn(`Element not found: ${selector}`);
      return;
    }

    const rect = el.getBoundingClientRect();

    // Создаём контейнер для выделения
    const highlight = document.createElement('div');
    highlight.className = 'docs-highlight-overlay';
    highlight.style.cssText = `
      position: fixed;
      border: ${config.width}px solid ${config.color};
      border-radius: 8px;
      pointer-events: none;
      z-index: 99999;
      box-shadow: 0 0 0 4px rgba(254, 141, 0, 0.2);
      transition: all 0.2s ease;
    `;

    highlight.style.left = `${rect.left - config.padding}px`;
    highlight.style.top = `${rect.top - config.padding}px`;
    highlight.style.width = `${rect.width + config.padding * 2}px`;
    highlight.style.height = `${rect.height + config.padding * 2}px`;

    // Добавляем метку если указана
    if (config.label !== null && config.label !== undefined) {
      const labelEl = document.createElement('div');
      labelEl.textContent = String(config.label);
      labelEl.style.cssText = `
        position: absolute;
        background: ${config.color};
        color: white;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        font-family: Inter, sans-serif;
        white-space: nowrap;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
      `;

      // Позиционирование метки
      switch (config.labelPosition) {
        case 'top':
          labelEl.style.bottom = '100%';
          labelEl.style.left = '0';
          labelEl.style.marginBottom = '8px';
          break;
        case 'bottom':
          labelEl.style.top = '100%';
          labelEl.style.left = '0';
          labelEl.style.marginTop = '8px';
          break;
        case 'left':
          labelEl.style.right = '100%';
          labelEl.style.top = '0';
          labelEl.style.marginRight = '8px';
          break;
        case 'right':
          labelEl.style.left = '100%';
          labelEl.style.top = '0';
          labelEl.style.marginLeft = '8px';
          break;
      }

      highlight.appendChild(labelEl);
    }

    document.body.appendChild(highlight);
  }, { selector, config });
}

/**
 * Удаляет все выделения со страницы
 */
export async function clearHighlights(page: Page): Promise<void> {
  await page.evaluate(() => {
    document.querySelectorAll('.docs-highlight-overlay').forEach(el => el.remove());
  });
}

/**
 * Добавляет нумерованные аннотации к нескольким элементам
 */
export async function annotateElements(
  page: Page,
  elements: Array<{ selector: string; label?: string }>
): Promise<void> {
  for (let i = 0; i < elements.length; i++) {
    const { selector, label } = elements[i];
    await highlightElement(page, selector, {
      label: label || (i + 1),
      labelPosition: 'top',
    });
  }
}

/**
 * Делает скриншот области вокруг элемента
 */
export async function screenshotElement(
  page: Page,
  selector: string,
  outputPath: string,
  padding: number = 40
): Promise<void> {
  const element = await page.$(selector);
  if (!element) {
    throw new Error(`Element not found: ${selector}`);
  }

  const box = await element.boundingBox();
  if (!box) {
    throw new Error(`Could not get bounding box for: ${selector}`);
  }

  await page.screenshot({
    path: outputPath,
    clip: {
      x: Math.max(0, box.x - padding),
      y: Math.max(0, box.y - padding),
      width: box.width + padding * 2,
      height: box.height + padding * 2,
    },
  });
}
