module.exports = {
  plugins: [
    require('tailwindcss'),
    require('postcss-custom-properties')({
      // 保留CSS变量，同时添加备用值
      preserve: true,
      // 导入CSS变量定义
      importFrom: [
        {
          customProperties: {
            '--color-primary': '#ef4444',
            '--color-primary-dark': '#dc2626',
            '--color-primary-light': '#f87171',
            '--color-gray-500': '#6b7280',
            '--color-gray-600': '#4b5563',
            '--color-white': '#ffffff',
            '--spacing-sm': '0.5rem',
            '--spacing-md': '1rem',
            '--spacing-lg': '1.5rem',
            '--border-radius-md': '0.375rem',
            '--shadow-sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)'
          }
        }
      ]
    }),
    require('autoprefixer')
  ]
}