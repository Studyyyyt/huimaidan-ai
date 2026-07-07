let timer = null;

export default {
  data() {
    return {
      containerWidth: 0
    };
  },
  created() {
    window.addEventListener('resize', this.handleContainerResize)
  },
  computed: {
    containerStyle() {
      return {
        '--container-width': this.containerWidth + 'px'
      };
    },
    isCollapse() {
      return this.$store.state.themeConfig.themeConfig.isCollapse;
    }
  },
  watch: {
    isCollapse() {
      if (timer) clearTimeout(timer);
      timer = setTimeout(() => {
        timer = null;
        this.handleContainerResize();
      }, 200);
    }
  },
  mounted() {
    this.handleContainerResize()
  },
  destroyed() {
    window.removeEventListener('resize', this.handleContainerResize)
  },
  methods: {
    handleContainerResize() {
      this.containerWidth = this.$el.closest(`.el-main`).clientWidth;
    }
  }
}