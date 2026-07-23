import js from '@eslint/js';
import prettier from 'eslint-config-prettier';
import vue from 'eslint-plugin-vue';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    {
        ignores: ['vendor/**', 'node_modules/**', 'public/build/**', 'bootstrap/cache/**', 'storage/**'],
    },
    js.configs.recommended,
    ...tseslint.configs.recommended,
    ...vue.configs['flat/recommended'],
    {
        files: ['**/*.vue'],
        languageOptions: {
            parserOptions: {
                parser: tseslint.parser,
            },
        },
    },
    {
        // Inertia page components are route-mirroring entry points, not
        // reusable components -- exempt from the multi-word rule per the
        // Vue style guide's own carve-out for root/page components.
        files: ['resources/js/Pages/**/*.vue'],
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
    prettier,
);
