<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_accessibility;

/**
 * Selector contract of styles.css: every feature value has a rule, and the panel follows the user (choices spec §1,
 * §3) except for alignment, line width, links, images and the reading guide.
 *
 * @package    local_accessibility
 * @category   test
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class styles_test extends \advanced_testcase {
    /**
     * The stylesheet without comments.
     *
     * @return string
     */
    private static function css(): string {
        $css = file_get_contents(__DIR__ . '/../styles.css');
        return preg_replace('~/\*.*?\*/~s', '', $css);
    }

    /**
     * Innermost rule blocks as [selector, body] pairs (rules nested in @media are found too).
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private static function rules(): array {
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', self::css(), $m, PREG_SET_ORDER);
        return array_map(fn($r) => [trim($r[1]), $r[2]], $m);
    }

    /**
     * A rule's selector without the comment that precedes it.
     *
     * @param string $selector
     * @return string
     */
    private static function selector_only(string $selector): string {
        return trim(preg_replace('~/\*.*?\*/~s', '', $selector));
    }

    /**
     * The rules whose selector contains a string.
     *
     * @param string $needle
     * @return array<int, array{0: string, 1: string}>
     */
    private static function rules_with(string $needle): array {
        return array_values(array_filter(self::rules(), fn($r) => str_contains($r[0], $needle)));
    }

    /**
     * The old fixed-value selectors are gone.
     */
    public function test_no_old_value_selectors(): void {
        $css = self::css();
        foreach (
            ['data-a11y-spacing', 'data-a11y-align="on"', 'data-a11y-links="on"', 'data-a11y-images="on"',
                'data-a11y-focus="cursor"', 'data-a11y-size="'] as $old
        ) {
            $this->assertStringNotContainsString($old, $css, $old);
        }
        // The phone size cap is dropped (D3): the user's size is applied as chosen.
        $this->assertStringNotContainsString('max-width: 480px', $css);
    }

    /**
     * Every new variable and value has a rule.
     */
    public function test_new_variables_and_values(): void {
        $css = self::css();
        foreach (
            ['var(--a11y-size)', 'var(--a11y-font)', 'var(--a11y-lh)', 'var(--a11y-ls)', 'var(--a11y-ws)',
                'var(--a11y-measure)', 'data-a11y-cursor="large"', 'data-a11y-cursor="xlarge"',
                'data-a11y-images="hide"', 'data-a11y-images="dim"', 'data-a11y-links="underline"',
                'data-a11y-links="outline"', 'data-a11y-links="highlight"', 'data-a11y-focus="ring"',
                'data-a11y-focus="thick"', 'data-a11y-align="left"', 'data-a11y-align="center"',
                'data-a11y-align="right"', 'data-a11y-align="justify"', 'pix/cursor.svg', 'pix/cursor-xlarge.svg',
                'data-a11y-lineheight', 'data-a11y-letterspacing', 'data-a11y-wordspacing', 'data-a11y-narrow'] as $new
        ) {
            $this->assertStringContainsString($new, $css, $new);
        }
        $size = array_values(array_filter(self::rules_with('html[data-a11y-size]'), fn($r) => !str_contains($r[0], 'font[')));
        $this->assertCount(1, $size);
        $this->assertStringContainsString('font-size: calc(var(--a11y-size) * 1%)', $size[0][1]);
        // Legacy <font size> 1-7 gets rem sizes, which follow the root (issue #2).
        $fonts = array_filter(self::rules_with('html[data-a11y-size]'), fn($r) => str_contains($r[0], 'font['));
        $this->assertCount(7, $fonts);
        foreach ($fonts as [$selector, $declarations]) {
            $this->assertMatchesRegularExpression('/^font-size: [\d.]+rem;?$/', trim($declarations), $selector);
        }
        // Paragraph spacing follows line height (D4).
        $para = array_filter(self::rules_with('[data-a11y-lineheight]'), fn($r) => str_contains($r[1], 'margin-block-end'));
        $this->assertCount(1, $para);
        $this->assertStringContainsString('calc(var(--a11y-lh) * 1.33em)', reset($para)[1]);
    }

    /**
     * Feature ids whose rules keep the panel and the readbar out.
     *
     * @return array
     */
    public static function excluded_provider(): array {
        return ['align' => ['data-a11y-align'], 'narrow' => ['data-a11y-narrow'], 'links' => ['data-a11y-links'],
            'images' => ['data-a11y-images'], 'guide' => ['data-a11y-guide']];
    }

    /**
     * Alignment, line width, links and images never apply inside the panel or the readbar.
     *
     * @dataProvider excluded_provider
     * @param string $attr
     */
    public function test_panel_excluded(string $attr): void {
        $rules = self::rules_with($attr);
        if ($attr === 'data-a11y-guide') {
            // The guide is an overlay drawn by guide.js; it has no attribute rule to guard.
            $this->assertCount(0, $rules);
            return;
        }
        $this->assertNotEmpty($rules, $attr);
        foreach ($rules as [$selector]) {
            $this->assertStringContainsString('.local-accessibility-panel *', $selector, $selector);
            $this->assertStringContainsString('.la-readbar *', $selector, $selector);
        }
    }

    /**
     * Feature ids whose rules the panel and the readbar follow.
     *
     * @return array
     */
    public static function followed_provider(): array {
        $ids = ['font', 'lineheight', 'letterspacing', 'wordspacing', 'focus', 'cursor', 'motion', 'colour'];
        return array_combine($ids, array_map(fn($id) => ['data-a11y-' . $id], $ids));
    }

    /**
     * Font, spacing, focus, cursor, motion and colour apply inside the panel and the readbar (R19 replaced).
     *
     * @dataProvider followed_provider
     * @param string $attr
     */
    public function test_panel_follows(string $attr): void {
        $rules = self::rules_with($attr);
        $this->assertNotEmpty($rules, $attr);
        $excluded = array_column(self::excluded_provider(), 0);
        foreach ($rules as [$selector, $body]) {
            foreach ($excluded as $other) {
                if (str_contains($selector, $other)) {
                    // A combined rule (e.g. scheme plus link highlight) belongs to the excluded feature.
                    continue 2;
                }
            }
            if ($attr === 'data-a11y-lineheight' && str_contains($body, 'margin-block-end')) {
                // Paragraph spacing follows line height but stays out of the panel: it would only add empty space
                // under the panel's own sample lines. The line height itself still applies there.
                $this->assertStringContainsString('.local-accessibility-panel *', $selector, $selector);
                continue;
            }
            $this->assertStringNotContainsString('.local-accessibility-panel *', $selector, $selector);
            $this->assertStringNotContainsString('.la-readbar *', $selector, $selector);
        }
    }

    /**
     * The panel no longer re-points the scheme's tokens to Boost's values.
     */
    public function test_no_panel_token_repointing(): void {
        foreach (self::rules_with('.local-accessibility-panel') as [$selector, $body]) {
            // A declaration of a Bootstrap token, not a var() reading one.
            $this->assertDoesNotMatchRegularExpression('/--bs-[a-z-]+\s*:/', $body, $selector);
        }
    }

    /**
     * The surface mop-up covers the activity header, which Boost paints light grey on every module page.
     */
    public function test_surface_mop_up(): void {
        $mopup = self::rules_with('html[data-a11y-colour] body :where(#page, #page-wrapper');
        $this->assertCount(1, $mopup, 'one rule repaints the theme\'s hardcoded surfaces');
        $this->assertStringContainsString('background-color: var(--a11y-surface1) !important', $mopup[0][1]);
        $parts = ['.card', '.activity-item', '.activity-header', '.course-content .section', '.table', '.dropdown-menu'];
        foreach ($parts as $part) {
            $this->assertStringContainsString($part . ',', $mopup[0][0] . ',', $part);
        }
        $blend = self::rules_with('html[data-a11y-colour] body :where(.badge, .activityiconcontainer)');
        $this->assertCount(1, $blend, 'badges and activity icons stop multiplying into the surface');
        $this->assertStringContainsString('mix-blend-mode: normal !important', $blend[0][1]);
    }

    /**
     * The panel is sized in rem/em, compact on wide screens and a bottom sheet on narrow ones (spec §1, D10).
     */
    public function test_panel_sizing(): void {
        $panel = array_values(array_filter(self::rules(), fn($r) => $r[0] === '.local-accessibility-panel'));
        $this->assertNotEmpty($panel);
        $body = implode("\n", array_column($panel, 1));
        $this->assertStringContainsString('clamp(380px, 50vw, 760px)', $body);
        $this->assertStringContainsString('min(80vh, calc(100vh - var(--la-panel-end) - 1rem))', $body);
        // At very large text sizes it keeps the lower half of the viewport (its bottom never above the middle).
        $this->assertStringContainsString('--la-panel-end: min(calc(var(--la-rest-top) + var(--la-offset)), calc(50vh - 1rem));', $body);
        $this->assertStringContainsString('font-size: 1rem', $body);
        // The scheme's colour first, then the Moodle Design System token (5.3), then Bootstrap's, then Boost's value.
        $this->assertStringContainsString('var(--a11y-page, var(--mds-bg-surface-default, var(--bs-body-bg, #fff)))', $body);
        $this->assertStringContainsString('var(--a11y-text, var(--mds-text-default, var(--bs-body-color, #1d2125)))', $body);
        $head = array_values(array_filter(self::rules(), fn($r) => $r[0] === '.local-accessibility-panel .la-head'));
        $this->assertNotEmpty($head);
        $this->assertStringContainsString('position: sticky', $head[0][1]);
        // The bottom sheet spans the viewport between its insets, not 100vw, which would include a classic scrollbar
        // and put the panel's edge (and the Close button's padding) under it.
        $this->assertSame(
            1,
            preg_match('/@media \(max-width: 767\.98px\)\s*\{\s*\.local-accessibility-panel\s*\{([^}]*)\}/', self::css(), $sheet)
        );
        $this->assertStringContainsString('inset-inline: 0;', $sheet[1]);
        $this->assertStringContainsString('inline-size: auto;', $sheet[1]);
        $this->assertStringNotContainsString('100vw', $sheet[1]);
        // The header wraps instead of pushing Close off a narrow screen, and the title's push is logical (RTL).
        $this->assertStringContainsString('flex-wrap: wrap', $head[0][1]);
        $title = array_values(array_filter(self::rules(), fn($r) => $r[0] === '.local-accessibility-panel .la-title'));
        $this->assertNotEmpty($title);
        $this->assertStringContainsString('margin-inline-end: auto', $title[0][1]);
        // The sticky header is bounded independently of the user's text size and line height, so at 300% text on a
        // short screen it cannot cover the settings under it: its size is capped by the viewport, its padding is in em
        // of that capped size, and its line height follows the user's only up to 1.5 (WCAG 1.4.12).
        $this->assertStringContainsString('--la-head-size: min(1rem, max(16px, 4.5vmin));', $head[0][1]);
        $this->assertStringContainsString('font-size: var(--la-head-size);', $head[0][1]);
        $this->assertDoesNotMatchRegularExpression('/(padding|gap):[^;]*rem/', $head[0][1]);
        $btn = array_values(array_filter(self::rules(), fn($r) => $r[0] === '.local-accessibility-panel .la-head .btn'));
        $this->assertNotEmpty($btn);
        foreach ([$title[0][1], $btn[0][1]] as $declarations) {
            $this->assertMatchesRegularExpression('/line-height: min\(var\(--a11y-lh, 1\.(25|5)\), 1\.5\);/', $declarations);
            $this->assertDoesNotMatchRegularExpression('/(font-size|padding):[^;]*rem/', $declarations);
        }
        $this->assertDoesNotMatchRegularExpression('/margin:\s*0 auto 0 0/', $title[0][1]);
        foreach (array_merge(self::rules_with('.local-accessibility-panel'), self::rules_with('.la-readbar')) as $rule) {
            [$selector, $declarations] = $rule;
            $this->assertDoesNotMatchRegularExpression('/font-size:\s*\d+(\.\d+)?px/', $declarations, $selector);
        }
    }

    /**
     * The floating launcher sits below chat and other docked panels (z-index 777) and is sized and placed in rem, so it
     * grows with the user's text size and keeps its place above Boost's footer button, which is placed in rem too.
     */
    public function test_launcher_follows_text_size(): void {
        $launcher = array_values(array_filter(self::rules(), fn($r) => $r[0] === '.btn.local-accessibility-launcher'));
        $this->assertNotEmpty($launcher);
        $body = $launcher[0][1];
        $this->assertStringContainsString('z-index: 777;', $body);
        foreach (['inline-size: 2.75rem;', 'block-size: 2.75rem;', 'font-size: 1.25rem;'] as $rule) {
            $this->assertStringContainsString($rule, $body);
        }
        // Its place: the offset above core's resting spot, in rem and px (launcher_test).
        $this->assertStringContainsString('inset-block-end: calc(var(--la-rest-top) + var(--la-offset));',
            implode("\n", array_column($launcher, 1)));
        foreach (self::rules_with('.local-accessibility-launcher') as [$selector, $declarations]) {
            $this->assertDoesNotMatchRegularExpression(
                '/(?<![-\w])(font-size|inline-size|block-size|inset-block-end):\s*\d+(\.\d+)?px/',
                $declarations,
                $selector
            );
        }
        $icon = array_values(array_filter(self::rules(), fn($r) => $r[0] === '.btn.local-accessibility-launcher .icon'));
        $this->assertNotEmpty($icon);
        $this->assertStringContainsString('max-inline-size: none', $icon[0][1]);
        $this->assertStringContainsString('max-block-size: none', $icon[0][1]);
    }

    /**
     * Icons in the plugin's UI grow with the text: Boost caps every .icon at 30px wide and 24px tall
     * (theme/boost/scss/moodle/icons.scss), which would let a rem-sized glyph overflow its box from 150% up.
     */
    public function test_ui_icons_uncapped(): void {
        foreach (['.local-accessibility-panel .icon', '.la-readbar .icon'] as $needle) {
            $uncapped = array_filter(
                self::rules(),
                fn($r) => in_array($needle, array_map('trim', explode(',', $r[0])), true)
                    && str_contains($r[1], 'max-inline-size: none') && str_contains($r[1], 'max-block-size: none')
            );
            $this->assertNotEmpty($uncapped, $needle);
        }
    }

    /**
     * The panel and the readbar take the user's whole spacing: buttons, headings and form controls, which the
     * browser and Bootstrap reset, inherit letter and word spacing, and fixed line heights are only fallbacks for
     * when no line height is chosen. The colour swatches are fixed-size previews and keep theirs.
     */
    public function test_ui_follows_spacing(): void {
        foreach (['.local-accessibility-panel', '.la-readbar'] as $root) {
            $inherit = array_filter(
                self::rules(),
                fn($r) => str_contains($r[0], $root . ' :where(button')
                    && str_contains($r[1], 'letter-spacing: inherit') && str_contains($r[1], 'word-spacing: inherit')
            );
            $this->assertNotEmpty($inherit, $root);
        }
        foreach (array_merge(self::rules_with('.local-accessibility-panel'), self::rules_with('.la-readbar')) as $rule) {
            [$selector, $declarations] = $rule;
            if (str_contains($selector, '.la-swatch')) {
                continue;
            }
            if (preg_match_all('/(?<![-\w])line-height:\s*([^;]+);/', $declarations, $m)) {
                foreach ($m[1] as $value) {
                    // The sticky header caps the user's line height at 1.5 so it cannot cover the panel (spec section 1).
                    if (str_contains($selector, '.la-head') || str_contains($selector, '.la-title')) {
                        $capped = '/^min\(var\(--a11y-lh, [0-9.]+\), 1\.5\)$/';
                        $this->assertMatchesRegularExpression($capped, trim($value), $selector);
                        continue;
                    }
                    $this->assertStringStartsWith('var(--a11y-lh,', trim($value), $selector);
                }
            }
        }
    }

    /**
     * Dimmed images fade once: a <picture> is not dimmed as well as its <img>, which would multiply to 0.16.
     */
    public function test_dim_not_doubled(): void {
        $dim = array_filter(self::rules_with('data-a11y-images="dim"'), fn($r) => str_contains($r[1], 'opacity: 0.4'));
        $this->assertCount(1, $dim);
        // The element, not .userpicture.
        $this->assertDoesNotMatchRegularExpression('/(?<![\w.-])picture\b/', reset($dim)[0]);
    }

    /**
     * Photos drawn as inline background images (format_tiles, issue #3) are dropped for "hide" and veiled, not faded,
     * for "dim", so the text on them keeps its contrast; the veil lifts on hover and focus.
     */
    public function test_background_images(): void {
        $bg = '[style*="background" i][style*="url(" i]';
        $hide = array_filter(self::rules_with('data-a11y-images="hide"'), fn($r) => str_contains($r[0], $bg));
        $this->assertCount(1, $hide);
        $this->assertStringContainsString('background-image: none !important', reset($hide)[1]);
        $dim = array_filter(self::rules_with('data-a11y-images="dim"'), fn($r) => str_contains($r[0], $bg));
        $this->assertCount(1, $dim);
        [$selector, $body] = reset($dim);
        $this->assertStringContainsString(':not(:hover, :focus-within, a:hover *, a:focus *)', $selector);
        $this->assertStringContainsString('box-shadow: inset 0 0 0 100vmax', $body);
        $this->assertStringContainsString('var(--a11y-page,', $body);
        $this->assertStringNotContainsString('opacity', $body);
    }

    /**
     * Issue #4: under a scheme, the theme's hard-coded light grey on hovered, focused and current menu items gives way
     * to the scheme's hover surface, and navbar controls and their icons take the scheme's colours; a highlighted link's
     * icons and current-item underline follow its text; the large cursor applies over every control but text entry.
     */
    public function test_issue4_rules(): void {
        $rules = self::rules_with('data-a11y-colour');
        $menu = array_filter($rules, fn($r) => str_contains($r[0], ':where(.moremenu .nav-link, .mds-nav-pill):where(:hover'));
        $this->assertCount(1, $menu);
        [$selector, $body] = reset($menu);
        $this->assertStringContainsString(':where(:hover, :focus, .active)', $selector);
        $this->assertStringContainsString('background-color: var(--a11y-hover) !important', $body);
        $icons = array_filter($rules, fn($r) => str_contains($r[0], '.navbar :is(a, .popover-region-toggle, .btn) .icon'));
        $this->assertCount(1, $icons);
        $this->assertStringContainsString('color: inherit !important', reset($icons)[1]);
        $toggles = array_filter($rules, fn($r) => str_contains($r[0], '.navbar :is(.popover-region-toggle:not(a), .btn,'));
        $this->assertStringContainsString('color: var(--a11y-text) !important', reset($toggles)[1]);
        // Navbar links follow links by purpose: the id's weight must not paint a menu item in the link colour.
        $links = array_filter($rules, fn($r) => str_contains($r[0], '.navbar a:not(.btn, .dropdown-item)'));
        $this->assertCount(1, $links);
        $this->assertStringContainsString('color: var(--la-anchor, var(--a11y-link)) !important', reset($links)[1]);
        // Themes mark their navbar colours !important (Boost Union), so the navbar rules carry an id's weight.
        foreach (array_merge($icons, $toggles) as [$selector]) {
            $this->assertStringContainsString('body:not(#la-weight)', $selector);
        }

        // Messaging and notifications: the drawer, bubbles (with their tails), unread notifications and active pills.
        $bodies = [];
        foreach ($rules as [$selector, $body]) {
            $bodies[$selector] = $body;
        }
        $expect = ['.message-app)' => 'var(--a11y-surface1)', '.message-app .message.received)' => 'var(--a11y-surface2)',
            '.message-app .message.received .tail)' => 'border-bottom-color: var(--a11y-surface2)',
            '.message-app .message.send .tail)' => 'border-bottom-color: var(--a11y-hover)',
            '.content-item-container.unread)' => 'var(--a11y-surface2)',
            '.nav-pills .nav-link.active)' => 'color: var(--a11y-page)'];
        foreach ($expect as $target => $declaration) {
            $match = array_filter(array_keys($bodies), fn($selector) => str_contains($selector, ':where(' . $target));
            $this->assertCount(1, $match, $target);
            $this->assertStringContainsString($declaration, $bodies[reset($match)], $target);
        }

        $highlight = self::rules_with('data-a11y-links="highlight"');
        $icon = array_filter($highlight, fn($r) => str_contains($r[0], 'a:not(.btn, .qnbutton) .icon'));
        $this->assertCount(1, $icon);
        $this->assertStringContainsString('color: inherit !important', reset($icon)[1]);
        $underline = array_filter($highlight, fn($r) => str_contains($r[0], 'a.nav-link:is(.active'));
        $this->assertStringContainsString('border-bottom-color: currentcolor !important', reset($underline)[1]);

        foreach (['large', 'xlarge'] as $size) {
            $rules = self::rules_with('data-a11y-cursor="' . $size . '"');
            $cursor = array_values(array_filter($rules, fn($r) => str_contains($r[1], 'cursor:')));
            $this->assertCount(1, $cursor, $size);
            $selector = $cursor[0][0];
            // Text entry keeps its cursor; select, checkbox and radio are no longer excluded.
            $this->assertStringContainsString('textarea', $selector);
            $this->assertStringContainsString('[type="checkbox"]', $selector);
            $this->assertStringNotContainsString('select', $selector);
            $this->assertStringNotContainsString('*:not(input,', $selector);
        }
    }

    /**
     * The extra-large cursor exists at 96px, and images.js adds alt text for the "hide" value.
     */
    public function test_cursor_and_images_script(): void {
        $svg = file_get_contents(__DIR__ . '/../pix/cursor-xlarge.svg');
        $this->assertNotFalse($svg);
        $this->assertStringContainsString('width="96" height="96"', $svg);
        $js = file_get_contents(__DIR__ . '/../amd/src/images.js');
        $this->assertStringContainsString("'hide'", $js);
        $this->assertStringNotContainsString("=== 'on'", $js);
    }

    /**
     * Authored colours give way to the scheme: inline colours and backgrounds (the shorthand too) and legacy colour
     * attributes, in the main region and in authored text anywhere.
     */
    public function test_authored_colours_overridden(): void {
        $rules = array_filter(self::rules(), fn($r) => str_contains($r[0], 'font[color]'));
        $this->assertCount(1, $rules);
        [$selector, $declarations] = reset($rules);
        foreach (
            ['#region-main', '.no-overflow', '.text_to_html', '.qtext', '[style*="color"]', '[style*="background"]',
                '[bgcolor]', 'html[data-a11y-colour]'] as $part
        ) {
            $this->assertStringContainsString($part, $selector, $part);
        }
        $this->assertStringContainsString('color: var(--a11y-text) !important', $declarations);
        $this->assertStringContainsString('background-color: transparent !important', $declarations);
    }

    /**
     * inlinesize.js scales fixed inline font sizes against --a11y-size and skips editable regions (issue #2).
     */
    public function test_inline_size_script(): void {
        $js = file_get_contents(__DIR__ . '/../amd/src/inlinesize.js');
        $this->assertNotFalse($js);
        $this->assertStringContainsString("' * var(--a11y-size, 100) / 100)'", $js);
        $this->assertStringContainsString('[contenteditable]', $js);
        // Line heights everywhere; box heights in authored text only, never on media (follow-up to issue #2).
        $this->assertStringContainsString("'line-height': scaled(", $js);
        $this->assertStringContainsString("const BOXES = ['height', 'min-height', 'max-height'];", $js);
        $this->assertStringContainsString("const CONTENT = '.no-overflow, .text_to_html, .qtext';", $js);
        $this->assertMatchesRegularExpression('/const MEDIA = .*\\bimg\\b.*\\biframe\\b/', $js);
        $panel = file_get_contents(__DIR__ . '/../amd/src/panel.js');
        $this->assertStringContainsString("from 'local_accessibility/inlinesize'", $panel);
    }

    /**
     * A font option's label keeps its own font (.la-ownfont) but, not being a preview (.la-sample), follows the user's
     * line, letter and word spacing like every other label in the panel (choices spec §1).
     */
    public function test_font_labels_follow_spacing(): void {
        $font = self::rules_with('html[data-a11y-font]');
        $this->assertNotEmpty($font);
        foreach ($font as [$selector]) {
            $this->assertStringContainsString('.la-ownfont, .la-ownfont *', $selector);
        }
        foreach (['data-a11y-lineheight', 'data-a11y-letterspacing', 'data-a11y-wordspacing'] as $attr) {
            $rules = self::rules_with('html[' . $attr . ']');
            $this->assertNotEmpty($rules, $attr);
            foreach ($rules as [$selector]) {
                $this->assertStringNotContainsString('la-ownfont', $selector, $attr);
            }
        }
    }

    /**
     * The scheme tokens (border, focus, selection) each have their rules once, keyed on data-a11y-tokens and never on a
     * scheme id; edges fall back to the text colour where no border colour is declared.
     */
    public function test_scheme_token_rules(): void {
        $css = self::css();
        $this->assertStringNotContainsString('vscodehc', $css, 'no rule keyed on a scheme id');
        $border = self::rules_with('html[data-a11y-tokens~="border"] body :where(');
        $this->assertCount(1, $border);
        $this->assertStringContainsString('border: 1px solid var(--a11y-border) !important', $border[0][1]);
        $parts = ['.card', '.form-control', '.table td', '.btn-secondary', '.dropdown-menu', '.modal-content', '.activity-item',
            '.badge'];
        foreach ($parts as $part) {
            $this->assertStringContainsString($part, $border[0][0], $part);
        }
        $this->assertStringNotContainsString('.nav-tabs .nav-link', $border[0][0], 'tabs have their own rule');
        $separator = self::rules_with('html[data-a11y-tokens~="border"] body .section .activity');
        $this->assertCount(1, $separator, 'no separator line between boxed activities');
        $this->assertStringContainsString('border-top: 0 !important', $separator[0][1]);
        $tabs = self::rules_with('html[data-a11y-tokens~="border"] body .nav-tabs');
        $this->assertCount(3, $tabs, 'one line under the tab bar, a box per tab, a shared edge between neighbours');
        $this->assertStringContainsString('border-bottom: 1px solid var(--a11y-border) !important', $tabs[0][1]);
        $this->assertStringContainsString('border: 1px solid var(--a11y-border) !important', $tabs[1][1]);
        $this->assertStringNotContainsString('background-color', $tabs[1][1], 'a hovered tab keeps the hover surface');
        $this->assertStringContainsString('.nav-item + .nav-item .nav-link', $tabs[2][0]);
        $this->assertStringContainsString('border-left-color: transparent !important', $tabs[2][1], 'shared edge');
        $current = self::rules_with('html[data-a11y-tokens~="border"] body:not(#la-weight) .nav-tabs .nav-link.active');
        $this->assertCount(0, $current, 'the current tab has no box of its own: navigation items are not boxed');
        $plainlink = ':where(a:not(.nav-link, .mds-nav-pill)):where(:focus, .focus)';
        $nofill = self::rules_with('html[data-a11y-tokens~="focus"] body ' . $plainlink);
        $this->assertCount(1, $nofill, 'a focused plain link loses the theme fill and shadow; menu items keep their surface');
        $this->assertStringContainsString('background-color: transparent !important', $nofill[0][1]);
        $css = self::css();
        $activeat = strpos($css, 'body:not(#la-weight) :where(.page-item.active > .page-link');
        $this->assertLessThan($activeat, strpos($css, $plainlink), 'before the chosen rule');
        $token = self::rules_with('html[data-a11y-tokens~="focus"]');
        $focus = array_values(array_filter($token, fn($r) => str_contains($r[0], ':focus-visible')));
        $this->assertCount(3, $focus);
        $this->assertStringContainsString('outline: 2px dashed var(--a11y-focus) !important', $focus[0][1]);
        $this->assertStringContainsString('outline-offset: var(--la-focus-offset, -2px) !important', $focus[0][1], 'the ring sits inside');
        $this->assertStringContainsString(':focus-visible', $focus[0][0]);
        $this->assertStringNotContainsString(':hover', implode('', array_column($token, 0)), 'the pointer gets no ring');
        $this->assertStringNotContainsString('solid var(--a11y-focus)', self::css(), 'focus is never solid');
        $this->assertStringContainsString('[data-a11y-focus="ring"]', $focus[1][0]);
        $this->assertStringContainsString('outline-width: 3px !important', $focus[1][1]);
        $this->assertStringContainsString('outline-offset: -3px !important', $focus[1][1]);
        $this->assertStringContainsString('box-shadow: none !important', $focus[1][1], 'no halo inside');
        $this->assertStringContainsString('[data-a11y-focus="thick"]', $focus[2][0]);
        $this->assertStringContainsString('outline-width: 5px !important', $focus[2][1]);
        $this->assertStringContainsString('outline-offset: -5px !important', $focus[2][1]);
        $this->assertStringContainsString('outline-offset: var(--la-focus-offset, -2px) !important', $focus[0][1],
            'a current item moves the ring in through the variable the chosen rule sets');
        $this->assertCount(0, self::rules_with('body:not(#la-weight) :where(.nav-link.active, .nav-link[aria-current]'),
            'no second list of current items');
        $this->assertStringNotContainsString('outline-offset: 2px !important', implode('', array_column($token, 1)));
        $selection = self::rules_with('html[data-a11y-tokens~="selection"]');
        $this->assertCount(1, $selection);
        $this->assertStringContainsString('::selection', $selection[0][0]);
        $this->assertStringContainsString('var(--a11y-selection) !important', $selection[0][1]);
        $this->assertStringContainsString('var(--a11y-selection-text) !important', $selection[0][1]);
        // Edges fall back to the text colour, as before the border token existed.
        $this->assertStringContainsString('--bs-border-color: var(--a11y-border, var(--a11y-text))', $css);
        $panel = implode(' ', array_column(array_filter(self::rules(), fn($r) => $r[0] === '.local-accessibility-panel'), 1));
        $this->assertStringContainsString('--la-edge: var(--a11y-border, var(--a11y-text, ', $panel);
        $this->assertStringContainsString('border: 1px solid var(--la-edge)', $panel);
    }

    /**
     * Theme-coloured controls: a plain .btn (no variant class) joins the list in both rules, the variants that keep the
     * theme's colours are excluded. The notification toggle is covered by the navbar controls rule (issue #4), not a
     * rule of its own.
     */
    public function test_theme_coloured_controls(): void {
        $plain = '.btn:not(.btn-primary, .btn-success, .btn-danger, .btn-warning, .btn-info, .btn-dark, .btn-link, '
            . '[class*="btn-outline-"])';
        $rules = self::rules_with($plain);
        $this->assertCount(2, $rules, 'the colour rule and the hover rule');
        $this->assertStringStartsWith('html[data-a11y-colour] body :where(.btn-secondary', $rules[0][0]);
        $this->assertStringContainsString('background-color: var(--a11y-surface2) !important', $rules[0][1]);
        $this->assertStringContainsString('color: var(--a11y-text) !important', $rules[0][1]);
        $this->assertStringContainsString(':where(:hover, :focus, .active, [aria-expanded="true"])', $rules[1][0]);
        $this->assertCount(0, self::rules_with('html[data-a11y-colour] body .popover-region-toggle'), 'one rule, upstream\'s');
    }

    /**
     * Links by purpose: the control list is stated once (--la-anchor) and the single anchor rule reads it.
     *
     */
    public function test_links_by_purpose(): void {
        $controls = array_values(array_filter(self::rules(), fn($r) => str_contains($r[1], '--la-anchor: var(--a11y-text)')));
        $this->assertCount(1, $controls, 'the control list is stated once');
        $selector = $controls[0][0];
        $this->assertStringStartsWith('html[data-a11y-colour] body :where(', $selector);
        $parts = ['nav', '[role="navigation"]', '[role="menu"]', '[role="menubar"]', '[role="tablist"]', '[role="tree"]',
            '[role="toolbar"]', 'h1', 'h6', 'thead th', '[role="button"]', '[role="tab"]', '[role="menuitem"]',
            '.dropdown-item', '.page-link', '.nav-link', '.badge', '.courseindex-link', 'a:has(> :is(h1, h2, h3, h4, h5, h6))',
            '.activity-item .activityname'];
        foreach ($parts as $part) {
            $this->assertStringContainsString($part, $selector, $part);
        }
        $this->assertStringContainsString('--la-anchor-line: none', $controls[0][1]);
        $anchors = array_values(array_filter(self::rules(), fn($r) => str_contains($r[1], 'var(--la-anchor, var(--a11y-link))')));
        // The anchor rule, and the 3.0.8 navbar and popover links rules, which read the same value so their id's weight
        // agrees with it.
        $this->assertCount(4, $anchors, 'the anchor rule, the link-styled buttons rule and the navbar and popover links rules');
        $this->assertStringStartsWith('html[data-a11y-colour] body :where(.btn-link)', self::selector_only($anchors[1][0]));
        $this->assertStringNotContainsString('text-decoration', $anchors[1][1], 'a link-styled button is not underlined');
        $this->assertStringContainsString('.navbar a:not(.btn, .dropdown-item)', $anchors[2][0]);
        $this->assertStringContainsString('.popover-region-container :is(a, .btn-link)', $anchors[3][0]);
        $this->assertStringContainsString('text-decoration: var(--la-anchor-line, underline) !important', $anchors[0][1]);
        // Buttons and the active page or menu item stay out of it, as before.
        foreach (['.btn', '.qnbutton', '.nav-link.active', '.page-item.active .page-link', '.dropdown-item.active'] as $part) {
            $this->assertStringContainsString($part, $anchors[0][0], $part);
        }
        $scheme = array_filter(self::rules(), fn($r) => str_starts_with($r[0], 'html[data-a11y-colour] body'));
        $bodies = implode(' ', array_column($scheme, 1));
        $this->assertStringNotContainsString('color: var(--a11y-link) !important', $bodies, 'no rule paints every anchor');
    }

    /**
     * Activity icons: one filter rule per purpose, Boost's selector shape, pointing at the panel's SVG filter.
     *
     */
    public function test_activity_icon_filters(): void {
        $rules = self::rules_with('.activityiconcontainer.');
        $this->assertCount(6, $rules);
        foreach ($rules as [$selector, $body]) {
            $shape = '/^html\[data-a11y-colour\] body \.activityiconcontainer\.([a-z]+):not\(\.isbranded\) '
                . ':is\(\.activityicon, \.icon\):not\(\.nofilter\)$/';
            $this->assertMatchesRegularExpression($shape, $selector);
            preg_match('/\.activityiconcontainer\.([a-z]+)/', $selector, $m);
            $this->assertSame('filter: url("#la-icon-' . $m[1] . '");', trim($body));
        }
    }

    /**
     * Legacy tables (.generaltable without .table) take the scheme, with one hover surface for their rows.
     */
    public function test_legacy_tables(): void {
        $mopup = self::rules_with('html[data-a11y-colour] body :where(#page, #page-wrapper');
        $this->assertCount(1, $mopup, 'still one rule repaints the hardcoded surfaces');
        foreach (['.generaltable', '.generaltable tbody tr', '.generaltable td', '.generaltable th', '.generaltable .sticky-column'] as $part) {
            $this->assertStringContainsString($part, $mopup[0][0], $part);
        }
        $hover = self::rules_with('tbody tr:hover > :is(td, th)');
        $this->assertCount(1, $hover, 'one hover-surface rule');
        $this->assertStringContainsString('background-color: var(--a11y-hover) !important', $hover[0][1]);
        $this->assertStringContainsString('.maincalendar .calendarmonth .clickable:hover', $hover[0][0]);
    }

    /**
     * Pagination, the gradebook, the assignment grading table and the quiz editing page paint their own cells; they take the scheme.
     */
    public function test_page_specific_tables(): void {
        $mopup = self::rules_with('html[data-a11y-colour] body :where(#page, #page-wrapper');
        $this->assertCount(1, $mopup, 'still one rule repaints the hardcoded surfaces');
        foreach (['.page-link', '.gradeparent .cell', '.user-report-container', '.user-grade td', '.gradetree-wrapper td', '.gradingtable td', '.gradingtable th', '#page-mod-quiz-edit ul.slots li.activity', '#page-mod-quiz-edit .inplaceeditable', '.page-header-headings', '.fp-filename'] as $part) {
            $this->assertStringContainsString($part, $mopup[0][0], $part);
        }
    }

    /**
     * Alerts and other small pale panels take the second surface; an alert keeps a leading edge.
     */
    public function test_small_panels(): void {
        $panels = self::rules_with('html[data-a11y-colour] body :where(.alert, .pluginupdateinfo');
        $this->assertCount(1, $panels, 'one rule for small panels');
        $this->assertStringContainsString('background-color: var(--a11y-surface2) !important', $panels[0][1]);
        $this->assertStringContainsString('color: var(--a11y-text) !important', $panels[0][1]);
        foreach (['.submissionstatus,', '.submissionstatusdraft', '.submissionstatussubmitted', '.latesubmission', '.userinitials', '.bg-secondary', '.bg-primary-light', '.path-mod-book .btn-next'] as $part) {
            $this->assertStringContainsString($part, $panels[0][0], $part);
        }
        $edge = self::rules_with('html[data-a11y-colour] body :where(.alert)');
        $this->assertCount(1, $edge);
        $this->assertStringContainsString('border-inline-start: 4px solid var(--a11y-link) !important', $edge[0][1]);
        // The edge's type (solid, dotted, double) and colour come from the state marks (section 7a), not a hex colour.
        foreach (['.alert-warning', '.alert-danger', '.alert-success'] as $type) {
            $this->assertCount(0, self::rules_with('html[data-a11y-colour] body :where(' . $type), $type);
            $this->assertCount(1, self::rules_with('html[data-a11y-colour] body:not(#la-weight) :where(' . $type), $type);
        }
    }

    /**
     * Utility surfaces that core's dark mode repaints with more weight take the scheme through the id's weight.
     */
    public function test_weighted_utilities(): void {
        $rules = self::rules_with('html[data-a11y-colour] body:not(#la-weight) :where(.bg-white, .bg-light, .bg-body)');
        $this->assertCount(1, $rules);
        $this->assertStringContainsString('background-color: var(--a11y-surface1) !important', $rules[0][1]);
        $grey = self::rules_with('html[data-a11y-colour] body:not(#la-weight) :where(a.bg-secondary:hover');
        $this->assertCount(1, $grey, 'a hovered or focused grey button keeps the second surface');
        $this->assertStringContainsString('background-color: var(--a11y-surface2) !important', $grey[0][1]);
    }

    /**
     * Text the theme colours itself takes the scheme's text colour.
     */
    public function test_hardcoded_text_colours(): void {
        $rules = self::rules_with('html[data-a11y-colour] body :where(.text-muted');
        $this->assertCount(1, $rules, 'one rule for hard-coded text colours');
        $this->assertStringContainsString('color: var(--a11y-text) !important', $rules[0][1]);
        $parts = ['.text-dark:not(.badge)', '.dropdown-item-text', '.dropdown-header', 'button.dropdown-item', '.activity-dates',
            '.submissionnotgraded', '#plugins-control-panel .componentname', 'a.toggle-category i', '.btn-outline-dark',
            '#page-mod-quiz-edit .questionname', '.path-mod-book .btn-next .icon'];
        foreach ($parts as $part) {
            $this->assertStringContainsString($part, $rules[0][0], $part);
        }
    }

    /**
     * A theme that forces link colours with !important does not beat the scheme: the anchor rule carries one id's weight.
     */
    public function test_anchor_rule_outranks_theme_links(): void {
        $anchors = array_values(array_filter(self::rules(), fn($r) => str_contains($r[1], 'text-decoration: var(--la-anchor-line, underline) !important')));
        $this->assertCount(1, $anchors);
        $this->assertStringContainsString('html[data-a11y-colour] body:not(#la-weight) :where(a:not(.btn', $anchors[0][0]);
        $highlight = self::rules_with('html[data-a11y-colour][data-a11y-links="highlight"] body:not(#la-weight)');
        $this->assertCount(1, $highlight, 'highlighted links keep the link colour on their surface');
        $this->assertStringContainsString('color: var(--a11y-link) !important', $highlight[0][1]);
    }

    /**
     * Fields, selects and input-group add-ons take the field colour and an edge in the scheme's border or text colour.
     */
    public function test_fields(): void {
        $rules = self::rules_with('.custom-select, .input-group-text, select, textarea, input:not(');
        $this->assertCount(1, $rules);
        $this->assertStringContainsString('background-color: var(--a11y-field) !important', $rules[0][1]);
        $this->assertStringContainsString('border-color: var(--a11y-border, var(--a11y-text)) !important', $rules[0][1]);
    }

    /**
     * A scheme without a focus token draws keyboard focus in its mark colour (section 7a) and drops the theme's
     * focused-link fill.
     */
    public function test_focus_without_token(): void {
        $ring = self::rules_with('html[data-a11y-colour]:not([data-a11y-tokens~="focus"], [data-a11y-focus="ring"], [data-a11y-focus="thick"]) :focus-visible');
        $this->assertCount(1, $ring);
        $this->assertStringContainsString('outline: 2px solid var(--a11y-mark) !important', $ring[0][1]);
        [, $rules] = self::state_marks();
        $this->assertContains($ring[0], $rules, 'in section 7a');
        $this->assertStringContainsString('outline-offset: var(--la-focus-offset, -2px) !important', $ring[0][1]);
        $nofill = self::rules_with('html[data-a11y-colour]:not([data-a11y-tokens~="focus"]) body :where(a:not(.nav-link, .mds-nav-pill))');
        $this->assertCount(1, $nofill);
        $this->assertStringContainsString('background-color: transparent !important', $nofill[0][1]);
    }

    /**
     * The text editor's menu bar, toolbar and status bar take the scheme; its icons take the text colour.
     */
    public function test_editor_toolbar(): void {
        $bars = self::rules_with('html[data-a11y-colour] body :where(.tox .tox-editor-header');
        $this->assertCount(1, $bars);
        $this->assertStringContainsString('background-color: var(--a11y-surface2) !important', $bars[0][1]);
        $icons = self::rules_with('html[data-a11y-colour] body .tox :where(.tox-tbtn svg');
        $this->assertCount(1, $icons);
        $this->assertStringContainsString('fill: var(--a11y-text) !important', $icons[0][1]);
    }

    /**
     * Monochrome image icons outside an activity icon container are inverted under a dark scheme.
     */
    public function test_image_icons(): void {
        $rules = self::rules_with('img.icon:not(.nofilter, .userpicture), img.pluginicon, img.itemicon');
        $this->assertCount(1, $rules);
        $this->assertStringStartsWith('html[data-a11y-colour][data-bs-theme="dark"] body', self::selector_only($rules[0][0]));
        $this->assertStringContainsString(':not(.activityiconcontainer *', $rules[0][0]);
        $this->assertStringContainsString('filter: invert(1) hue-rotate(180deg) !important', $rules[0][1]);
    }

    /**
     * A highlighted search term takes the text colour on the hover surface, underlined.
     */
    public function test_search_highlight(): void {
        $rules = self::rules_with('html[data-a11y-colour] body :where(.highlight)');
        $this->assertCount(1, $rules);
        $this->assertStringContainsString('color: var(--a11y-text) !important', $rules[0][1]);
        $this->assertStringContainsString('background-color: var(--a11y-hover) !important', $rules[0][1]);
        $this->assertStringContainsString('text-decoration: underline !important', $rules[0][1]);
    }

    /**
     * Section 7a "State marks" comes after section 7, and the stylesheet names each state class in 7a once.
     *
     * @return array{0: string, 1: array<int, array{0: string, 1: string}>} the raw 7a text and its rules
     */
    private static function state_marks(): array {
        $raw = file_get_contents(__DIR__ . '/../styles.css');
        $start = strpos($raw, '/* 7a. State marks.');
        $end = strpos($raw, '/* 8. Links');
        $section = substr($raw, $start, $end - $start);
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', preg_replace('~/\*.*?\*/~s', '', $section), $m, PREG_SET_ORDER);
        return [$section, array_map(fn($r) => [trim($r[1]), $r[2]], $m)];
    }

    /**
     * The state classes 7a owns, by kind.
     *
     * @return array<string, string[]>
     */
    private static function state_classes(): array {
        return [
            'chosen' => ['.page-item.active > .page-link', '.list-group-item.active', '.btn.active', '[aria-pressed="true"]',
                '[data-region="chooser-option-container"])[aria-selected="true"]:not(:is([role="tree"], .courseindex) *)',
                '[role="tab"][aria-selected="true"]:not(:is([role="tree"], .courseindex) *)', '.nav-link.active', '.mds-nav-pill--selected', '.courseindex-item.pageitem',
                '.dropdown-item.active', '.tox-tbtn--enabled', '.today .day-number-circle', 'tr.selectedrow > *',
                '.breadcrumb-item:last-child > span:only-child'],
            'changed' => ['.quickgrademodified', 'td.ajaxoverridden'],
            'disabled' => ['.btn.disabled', '.dropdown-item.disabled', '.nav-link.disabled', '.list-group-item.disabled', '.page-item.disabled > .page-link', '.tox-tbtn--disabled'],
            'readonly' => ['[readonly]'],
            'status' => ['.gradepass', '.gradefail', 'td.table-success', 'td.table-warning', 'td.table-danger',
                'tr.table-success > *', 'tr.table-warning > *', 'tr.table-danger > *'],
            'alert' => ['.alert-success', '.alert-warning', '.alert-danger', '.alert-info'],
            'badge' => ['.badge:is(.bg-success, .badge-success, .text-bg-success)', '.badge:is(.bg-warning, .badge-warning, .text-bg-warning)',
                '.badge:is(.bg-danger, .badge-danger, .text-bg-danger)', '.badge:is(.bg-info, .badge-info, .text-bg-info)'],
            'progress' => ['.progress-bar', '.progress'],
            'hidden' => ['.dimmed_text', '.dimmed', '.usersuspended'],
        ];
    }

    /**
     * 7a comes after every section 7 rule and before section 8; it never applies in forced colours.
     */
    public function test_state_marks_follow_section_7(): void {
        $raw = file_get_contents(__DIR__ . '/../styles.css');
        $s7 = strpos($raw, '/* 7. Colour schemes');
        $s7a = strpos($raw, '/* 7a. State marks.');
        $s8 = strpos($raw, '/* 8. Links');
        $this->assertNotFalse($s7a);
        $this->assertGreaterThan($s7, $s7a);
        $this->assertLessThan($s8, $s7a);
        [$section] = self::state_marks();
        $this->assertStringContainsString('@media (forced-colors: none)', $section);
        $this->assertSame(1, substr_count($raw, '/* 7a. State marks.'));
    }

    /**
     * Each state class is named once in 7a (in the selector of its kind's rule), and 7a's rules all sit under a
     * colour scheme, so with no scheme chosen nothing changes.
     */
    public function test_state_classes_named_once(): void {
        [, $rules] = self::state_marks();
        // The status icon rule and the dot rule read --la-dot for every status kind, so they repeat the status text
        // classes: CSS cannot select an element by a custom property it carries (style queries are not in every browser).
        // The chosen kind's hover-surface twin repeats the chosen list too (VS Code keeps its own fills per element).
        $twin = 'html[data-a11y-colour]:not([data-a11y-tokens~="active"])';
        $owners = array_filter($rules, fn($r) => !(str_contains($r[0], '::before') && str_contains($r[0], '.text-success'))
            && !str_contains($r[0], ':where(i, .icon, .fa') && !str_starts_with($r[0], $twin));
        $this->assertCount(count($rules) - 3, $owners, 'only those three rules repeat them');
        $selectors = implode("\n", array_column($owners, 0));
        foreach (self::state_classes() as $kind => $classes) {
            foreach ($classes as $class) {
                $this->assertSame(1, substr_count($selectors, $class . ',') + substr_count($selectors, $class . ')'),
                    "$kind: $class named once");
            }
        }
        foreach ($rules as [$selector]) {
            $this->assertMatchesRegularExpression('/^html\[data-a11y-(colour|tokens)/', $selector, 'under a scheme');
        }
    }

    /**
     * No rule after 7a sets an edge, an outline, opacity or font style on a state class, or the content, background or
     * display of a status dot, so nothing undoes a mark.
     */
    public function test_nothing_after_state_marks_undoes_them(): void {
        $raw = file_get_contents(__DIR__ . '/../styles.css');
        $after = preg_replace('~/\*.*?\*/~s', '', substr($raw, strpos($raw, '/* 8. Links')));
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $after, $m, PREG_SET_ORDER);
        $classes = array_merge(...array_values(self::state_classes()));
        foreach ($m as $r) {
            foreach ($classes as $class) {
                if (!str_contains($r[1], $class)) {
                    continue;
                }
                $this->assertDoesNotMatchRegularExpression('/(^|[;\s])(box-shadow|outline|outline-color|outline-style|outline-width|opacity|font-style)\s*:/',
                    $r[2], trim($r[1]) . " sets a mark property on $class");
                if (str_contains($r[1], '::before')) {
                    $this->assertDoesNotMatchRegularExpression('/(^|[;\s])(content|background|background-color|display)\s*:/',
                        $r[2], trim($r[1]) . " changes the status dot of $class");
                }
            }
        }
    }

    /**
     * Chosen and current items: a 2px box inside the edge in the mark colour, the hover surface where the scheme has
     * no active colour of its own; a chosen row boxed as one; the 5.3 pill dot hidden.
     */
    public function test_state_marks_chosen(): void {
        [, $rules] = self::state_marks();
        $chosen = array_values(array_filter($rules, fn($r) => str_contains($r[0], '.page-item.active > .page-link')));
        $this->assertCount(2, $chosen, 'the navigation rule and the hover-surface twin');
        $this->assertStringNotContainsString('box-shadow', $chosen[0][1], 'navigation and links have no box');
        $this->assertStringContainsString('color: var(--a11y-text) !important', $chosen[0][1]);
        $this->assertStringNotContainsString('background-color', $chosen[0][1], 'VS Code keeps its own fills');
        $this->assertStringContainsString(':not([data-a11y-tokens~="active"])', $chosen[1][0]);
        $this->assertStringContainsString('background-color: var(--a11y-hover) !important', $chosen[1][1]);
        foreach (['.mds-nav-pill--selected', '.breadcrumb-item:last-child', '.courseindex-item.pageitem', '[aria-current]'] as $nav) {
            $this->assertStringContainsString($nav, $chosen[0][0], $nav . ': no box');
        }
        $boxed = array_values(array_filter($rules, fn($r) => str_contains($r[0], '.tox-tbtn--enabled')
            && !str_contains($r[0], 'data-a11y-tokens')));
        $this->assertCount(1, $boxed, 'pressed and selected controls');
        $this->assertStringContainsString('box-shadow: inset 0 0 0 2px var(--a11y-mark) !important', $boxed[0][1]);
        $this->assertStringContainsString('--la-focus-offset: -4px;', $boxed[0][1]);
        foreach (['.btn.active', '[aria-pressed="true"]', '[role="option"]', '.today .day-number-circle'] as $control) {
            $this->assertStringContainsString($control, $boxed[0][0], $control . ': boxed');
        }
        foreach (['.page-link', '.nav-link', '.mds-nav-pill', 'aria-current', '.courseindex', '.breadcrumb', '[role="tab"]'] as $nav) {
            $this->assertStringNotContainsString($nav . '.', str_replace(':not(:is([role="tree"], .courseindex) *)', '', $boxed[0][0]), $nav);
        }
        $this->assertStringNotContainsString('font-weight', implode('', array_column($rules, 1)), 'no bold: it shifts layout');
        $rows = array_values(array_filter($rules, fn($r) => str_contains($r[0], 'tr.selectedrow')));
        $this->assertCount(3, $rows, 'every cell, the first and the last');
        $this->assertStringContainsString('inset 0 2px 0 var(--a11y-mark)', $rows[0][1]);
        $dot = array_values(array_filter($rules, fn($r) => str_contains($r[0], '.mds-nav-pill__indicator')));
        $this->assertStringContainsString('display: none !important', $dot[0][1]);
        $this->assertStringNotContainsString('--a11y-active)', file_get_contents(__DIR__ . '/../styles.css'),
            'the mark colour replaces the active token everywhere');
    }

    /**
     * State marks: changed.
     */
    public function test_state_marks_changed(): void {
        [, $rules] = self::state_marks();
        $r = array_values(array_filter($rules, fn($x) => str_contains($x[0], '.quickgrademodified')));
        $this->assertCount(1, $r);
        $this->assertStringContainsString('--la-status: var(--a11y-warn-mark);', $r[0][1]);
        $this->assertStringContainsString('--la-status-w: 2px;', $r[0][1], 'core colours a changed grade on every version');
        $this->assertStringContainsString('box-shadow: inset 0 0 0 var(--la-status-w) var(--la-status) !important', $r[0][1]);
    }

    /**
     * State marks: disabled.
     */
    public function test_state_marks_disabled(): void {
        [, $rules] = self::state_marks();
        $r = array_values(array_filter($rules, fn($x) => str_contains($x[0], '.dropdown-item.disabled')));
        $this->assertNotEmpty($r);
        $this->assertStringContainsString('opacity: 0.6 !important', $r[0][1]);
    }

    /**
     * State marks: status.
     */
    public function test_state_marks_status(): void {
        [, $rules] = self::state_marks();
        foreach (['.text-success' => 'ok', '.text-warning' => 'warn', '.text-danger' => 'bad'] as $class => $kind) {
            $r = array_values(array_filter($rules, fn($x) => str_contains($x[0], ':where(' . $class)));
            $this->assertStringContainsString('--la-dot: var(--a11y-' . $kind . '-mark);', $r[0][1], $class);
            $this->assertStringContainsString('color: var(--a11y-text) !important', $r[0][1], $class . ': the words keep the text colour');
        }
        $dots = array_values(array_filter($rules, fn($x) => str_contains($x[0], '::before') && str_contains($x[0], '.text-success')));
        $this->assertCount(1, $dots, 'one dot rule');
        $this->assertStringContainsString('content: "" !important', $dots[0][1], 'nothing for assistive technology to read');
        $this->assertStringContainsString('background-color: var(--la-dot) !important', $dots[0][1]);
        $this->assertStringContainsString('display: var(--la-dot-display, inline-block) !important', $dots[0][1]);
        foreach (['i, .icon, .fa, [class*="fa-"], svg, img', '.badge', ':has('] as $excluded) {
            $this->assertStringContainsString($excluded, $dots[0][0], 'no dot on ' . $excluded);
        }
        $icons = array_values(array_filter($rules, fn($x) => str_contains($x[0], ':where(i, .icon, .fa')));
        $this->assertCount(1, $icons);
        $this->assertStringContainsString('color: var(--la-dot) !important', $icons[0][1], 'a status icon takes the mark colour');
        $cells = array_values(array_filter($rules, fn($x) => str_contains($x[0], 'td.table-success')));
        $this->assertStringContainsString('--la-dot-display: none;', $cells[0][1], 'no dot inside a bordered status cell');
    }

    /**
     * State marks: progress.
     */
    public function test_state_marks_progress(): void {
        [, $rules] = self::state_marks();
        $r = array_values(array_filter($rules, fn($x) => str_contains($x[0], '.progress-bar')));
        $this->assertNotEmpty($r);
        $this->assertStringContainsString('background-color: var(--a11y-mark) !important', $r[0][1]);
    }

    /**
     * State marks: hidden.
     */
    public function test_state_marks_hidden(): void {
        [, $rules] = self::state_marks();
        $r = array_values(array_filter($rules, fn($x) => str_contains($x[0], '.usersuspended')));
        $this->assertNotEmpty($r);
        $this->assertStringContainsString('font-style: italic !important', $r[0][1]);
        $this->assertStringContainsString('opacity: 1 !important', $r[0][1]);
    }

    /**
     * State marks: a read-only field is not faded; it keeps the field colour with a solid 2px edge.
     */
    public function test_state_marks_readonly(): void {
        [, $rules] = self::state_marks();
        $r = array_values(array_filter($rules, fn($x) => str_contains($x[0], '[readonly]')));
        $this->assertCount(1, $r);
        $this->assertStringContainsString(':is([readonly], :disabled)', $r[0][0], 'one look for read-only and disabled fields');
        foreach (['.form-control', '.form-select', '.custom-select', 'textarea', 'select'] as $field) {
            $this->assertStringContainsString($field, $r[0][0], $field);
        }
        $this->assertStringContainsString('border: 2px solid var(--a11y-text) !important', $r[0][1]);
        $this->assertStringContainsString('color: var(--a11y-text) !important', $r[0][1], 'full-strength text');
        $this->assertStringContainsString('opacity: 1 !important', $r[0][1], 'not faded');
    }

    /**
     * State marks: a disabled text field, textarea or select is not faded; buttons, checkboxes, radios and switches are.
     */
    public function test_state_marks_disabled_fields_not_faded(): void {
        [, $rules] = self::state_marks();
        $r = array_values(array_filter($rules, fn($x) => str_contains($x[1], 'opacity: 0.6 !important')
            && str_contains($x[0], ':disabled')));
        $this->assertCount(1, $r);
        $this->assertStringContainsString(':is(button, .btn, input:is([type="checkbox"], [type="radio"]', $r[0][0]);
        foreach (['textarea', 'select', '.form-control'] as $field) {
            $this->assertStringNotContainsString($field, $r[0][0], $field . ' is not faded');
        }
    }

    /**
     * State marks: aria-selected counts as chosen only on tabs, options and activity chooser options, never in a tree.
     */
    public function test_state_marks_aria_selected_scoped(): void {
        [$section] = self::state_marks();
        $plain = preg_replace('~/\*.*?\*/~s', '', $section);
        $this->assertSame(0, preg_match('/[,(]\s*\[aria-selected="true"\]/', $plain), 'no bare aria-selected');
        $notree = '[aria-selected="true"]:not(:is([role="tree"], .courseindex) *)';
        $this->assertSame(substr_count($plain, '[aria-selected="true"]'), substr_count($plain, $notree), 'never inside a tree');
        $this->assertSame(1, substr_count($plain, '[role="tab"]' . $notree), 'a tab: navigation, no box');
        $this->assertSame(1, substr_count($plain, ':is([role="option"], [role="gridcell"], [data-region="chooser-option-container"])'
            . $notree), 'an option: boxed');
    }

    /**
     * State marks: an alert's type is a solid 1em marker at its leading edge in the type's mark colour.
     */
    public function test_state_marks_alerts(): void {
        [, $rules] = self::state_marks();
        foreach (['.alert-success' => 'ok-mark', '.alert-warning' => 'warn-mark', '.alert-danger' => 'bad-mark', '.alert-info' => 'link'] as $class => $token) {
            $r = array_values(array_filter($rules, fn($x) => str_contains($x[0], $class)));
            $this->assertCount(1, $r, $class);
            $this->assertStringContainsString('border-inline-start: 1em solid var(--a11y-' . $token . ') !important', $r[0][1], $class);
            $this->assertStringContainsString('.badge:is(', $r[0][0], $class . ': one rule for the alert and the badge of that type');
        }
    }

    /**
     * State marks: badges and pills are painted like alerts: second surface, text colour, a 1px edge in the border or
     * text colour, and for a coloured badge a 1em leading marker in place of that side's edge; primary, light and dark
     * badges are left alone.
     */
    public function test_state_marks_badges(): void {
        [, $rules] = self::state_marks();
        $shape = array_values(array_filter($rules, fn($x) => str_ends_with($x[0], ':where(.badge:is([class*="bg-"], [class*="badge-"]):not(.bg-primary, .badge-primary, .text-bg-primary, .bg-light, .badge-light, .text-bg-light, .bg-dark, .badge-dark, .text-bg-dark, .correctness))')));
        $this->assertCount(1, $shape);
        $this->assertStringContainsString('background-color: var(--a11y-surface2) !important', $shape[0][1]);
        $this->assertStringContainsString('color: var(--a11y-text) !important', $shape[0][1]);
        $this->assertStringContainsString('border: 1px solid var(--a11y-border, var(--a11y-text)) !important', $shape[0][1]);
        $this->assertStringNotContainsString('--la-marker-width', file_get_contents(__DIR__ . '/../styles.css'), 'one marker width, 1em');
        $raw = file_get_contents(__DIR__ . '/../styles.css');
        $this->assertLessThan(strpos($raw, ':where(.alert-success, .badge:is('), strpos($raw, ':where(.badge:not(.bg-primary'),
            'the badge edge comes before the marker that replaces its leading side');
    }

    /**
     * State marks: a status cell has a solid 2px border in its status mark colour; a ticked row keeps its box and shows
     * that border inside it; no dashed, dotted or double line anywhere in section 7a.
     */
    public function test_state_marks_status_cells(): void {
        [$section, $rules] = self::state_marks();
        foreach (['td.table-success' => 'ok', 'td.table-warning' => 'warn', 'td.table-danger' => 'bad'] as $class => $kind) {
            $r = array_values(array_filter($rules, fn($x) => str_contains($x[0], $class)));
            $this->assertCount(1, $r, $class);
            $this->assertStringContainsString('--la-status: var(--a11y-' . $kind . '-mark);', $r[0][1]);
            $this->assertStringContainsString('--la-status-w: calc(2px * (1 + var(--breakpoint-xs, -1)));', $r[0][1],
                'a border only where core colours the cell: Bootstrap 4 defines --breakpoint-xs, Bootstrap 5 does not');
            $this->assertStringContainsString('box-shadow: inset 0 0 0 var(--la-status-w) var(--la-status) !important', $r[0][1]);
            $this->assertStringContainsString('background-color: transparent !important', $r[0][1]);
        }
        $this->assertStringNotContainsString('--breakpoint-xs:', self::css(), 'the plugin never sets the signal itself');
        $rows = array_values(array_filter($rules, fn($x) => str_contains($x[0], 'tr.selectedrow')));
        foreach ($rows as $row) {
            $this->assertStringContainsString('inset 0 0 0 calc(var(--la-status-w, 0px) * 2) var(--la-status, transparent)', $row[1],
                'both marks in a ticked row, the cell border following its width');
        }
        $css = preg_replace('~/\*.*?\*/~s', '', $section);
        foreach (['dashed', 'dotted', 'double'] as $style) {
            $this->assertStringNotContainsString($style, $css, "no $style line");
        }
        $status = strpos($css, 'td.table-success');
        $this->assertLessThan(strpos($css, 'tr.selectedrow'), $status, 'status cells come before the chosen rows that read --la-status');
    }

    /**
     * Behind the page: where the page has #page-wrapper, html and body take the wrapper's surface, so the area the
     * document scrolls past the one-viewport wrapper (the gutters beside a floating drawer) keeps the same colour.
     */
    public function test_page_wrapper_surface_behind_the_page(): void {
        $r = self::rules_with('html[data-a11y-colour]:has(#page-wrapper),');
        $this->assertCount(1, $r);
        $this->assertStringContainsString('html[data-a11y-colour]:has(#page-wrapper) body', $r[0][0]);
        $this->assertStringContainsString('background-color: var(--a11y-surface1) !important', $r[0][1]);
        $mopup = self::rules_with('html[data-a11y-colour] body :where(#page, #page-wrapper,');
        $this->assertStringContainsString('background-color: var(--a11y-surface1) !important', $mopup[0][1], 'the same surface');
    }

    /**
     * Plain controls core draws with no inline padding (combobox toggles, .btn.p-0) take 0.5em of inline padding under
     * a scheme; icon buttons and coloured variants are left out.
     */
    public function test_zero_padding_controls(): void {
        $r = self::rules_with('html[data-a11y-colour] body:not(#la-weight) :where(.btn:is(.p-0, .px-0, .dropdown-toggle[role="combobox"])');
        $this->assertCount(1, $r);
        $this->assertStringContainsString('padding-inline: 0.5em !important', $r[0][1]);
        foreach (['.btn-icon', '.btn-link', '.btn-primary', '.local-accessibility-launcher'] as $out) {
            $this->assertStringContainsString($out, $r[0][0], $out . ' keeps its padding');
        }
    }

    /**
     * The navbar takes the scheme's surface with an id's weight, over a theme's brand-coloured navbar.
     */
    public function test_navbar_scheme_surface(): void {
        $r = self::rules_with('html[data-a11y-colour] body:not(#la-weight) :where(.navbar)');
        $this->assertCount(1, $r);
        $this->assertStringContainsString('background-color: var(--a11y-surface2) !important', $r[0][1]);
        $this->assertStringContainsString('background-image: none !important', $r[0][1]);
    }
}
