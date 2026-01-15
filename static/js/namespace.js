/**
 * 命名空间管理系统
 * 避免全局变量污染，统一管理所有组件
 */
(function() {
    'use strict';
    
    // 创建主命名空间
    const XPK = {
        version: '1.0.0',
        components: {},
        utils: {},
        config: {},
        
        // 注册组件
        register(name, component) {
            if (this.components[name]) {
                throw new Error(`Component "${name}" already exists`);
            }
            this.components[name] = component;
            return this;
        },
        
        // 获取组件
        get(name) {
            return this.components[name];
        },
        
        // 注册工具函数
        registerUtil(name, util) {
            if (this.utils[name]) {
                throw new Error(`Utility "${name}" already exists`);
            }
            this.utils[name] = util;
            return this;
        },
        
        // 获取工具函数
        getUtil(name) {
            return this.utils[name];
        },
        
        // 设置配置
        setConfig(key, value) {
            this.config[key] = value;
            return this;
        },
        
        // 获取配置
        getConfig(key) {
            return this.config[key];
        },
        
        // 初始化所有组件
        init() {
            Object.keys(this.components).forEach(name => {
                const component = this.components[name];
                if (typeof component.init === 'function') {
                    component.init();
                }
            });
        },
        
        // 销毁所有组件
        destroy() {
            Object.keys(this.components).forEach(name => {
                const component = this.components[name];
                if (typeof component.destroy === 'function') {
                    component.destroy();
                }
            });
        }
    };
    
    // 只暴露一个全局变量
    if (typeof window !== 'undefined') {
        window.XPK = XPK;
    }
    
    // 兼容性：保留原有的xpk对象但作为XPK的别名
    if (typeof window !== 'undefined' && window.xpk) {
        // 将现有的xpk功能迁移到XPK命名空间
        XPK.registerUtil('toast', window.xpk.toast);
        XPK.registerUtil('confirm', window.xpk.confirm);
        XPK.registerUtil('fetch', window.xpk.fetch);
        XPK.registerUtil('setCookie', window.xpk.setCookie);
        XPK.registerUtil('getCookie', window.xpk.getCookie);
        XPK.registerUtil('deleteCookie', window.xpk.deleteCookie);
        
        // 保持向后兼容
        window.xpk = {
            toast: XPK.getUtil('toast'),
            confirm: XPK.getUtil('confirm'),
            fetch: XPK.getUtil('fetch'),
            setCookie: XPK.getUtil('setCookie'),
            getCookie: XPK.getUtil('getCookie'),
            deleteCookie: XPK.getUtil('deleteCookie')
        };
    }
})();