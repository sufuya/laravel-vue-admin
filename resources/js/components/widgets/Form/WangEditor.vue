<template>
  <div class="wangeditor-main flex-sub">
    <el-tooltip v-if="attrs.uploadVideoServer" class="item" effect="dark" content="上传视频" placement="top">
      <el-upload
          :action="attrs.uploadVideoServer"
          :data="videoUploadToken"
          :headers="attrs.uploadVideoHeaders"
          class="uploadBtn"
          :limit="1"
          accept=".mp4"
          :show-file-list="false"
          :on-success="handleSuccess"
          :file-list="fileList"
      >
        <i class="el-icon-video-camera"></i>
      </el-upload>
    </el-tooltip>
    <div ref="toolbar" class="toolbar"></div>
    <div v-if="attrs.component">
      <component
          :is="attrs.component.componentName"
          :attrs="attrs.component"
          :editor.sync="editor"
      />
    </div>
    <div ref="editor" :style="attrs.style" :class="attrs.className"></div>
  </div>
</template>
<script>
import E from "wangeditor";
import {FormItemComponent} from "@/mixins.js";

export default {
  mixins: [FormItemComponent],
  data() {
    return {
      editor: null,
      initHtml: false,
      defaultValue: "",
      videoUploadToken: {
        _token: ''
      },
      fileList: [],
      uploadVideoHeaders: []
    };
  },
  methods: {
    handleSuccess(response, file, fileList) { // el-upload上传成功后的回调， 在这里获取视频路径后添加video标签到editor标签中
      this.fileList = []
      let content = `<p><video src="${response.data[0]}" style="width:100%" controls autobuffer autoplay muted/><br></p><p>视频描述： </p>`
      this.editor.cmd.do('insertHTML', content)
    },
  },
  mounted() {
    this.videoUploadToken = {
      _token: Admin.token
    }
    this.defaultValue = this._.cloneDeep(this.attrs.componentValue);

    this.editor = new E(this.$refs.toolbar, this.$refs.editor);
    this.editor.customConfig.menus = this.attrs.menus;
    this.editor.customConfig.zIndex = this.attrs.zIndex;
    this.editor.customConfig.uploadImgShowBase64 = this.attrs.uploadImgShowBase64;
    if (this.attrs.uploadImgServer) {
      this.editor.customConfig.uploadImgServer = this.attrs.uploadImgServer;

      this.editor.customConfig.uploadImgParams = {
        _token: Admin.token
      };
    }
    //自定义 fileName
    if (this.attrs.uploadFileName) {
      this.editor.customConfig.uploadFileName = this.attrs.uploadFileName;
    }
    //自定义 header
    if (this.attrs.uploadImgHeaders) {
      this.editor.customConfig.uploadImgHeaders = this.attrs.uploadImgHeaders;
    }

    this.editor.customConfig.onchange = html => {
      this.onChange(html);
    };

    this.$nextTick(() => {
      this.editor.create();
      this.editor.txt.html(this.defaultValue);
    });
    //编辑数据加载完毕设置编辑器的值
    this.$bus.on("EditDataLoadingCompleted", () => {
      this.editor && this.editor.txt.html(this.value);
    });
    /**
     * 插入内容
     */
    this.$bus.on('EditorInsertHtml', (html => {
      this.editor.cmd.do('insertHTML', html)
    }))
  },
  destroyed() {
    try {
      this.$bus.off("EditDataLoadingCompleted");
    } catch (e) {
    }
  }
};
</script>
<style lang="scss" scoped>
.wangeditor-main {
  position: relative;
  border: 1px solid #dcdcdc;

  .toolbar {
    background: #f7f7f7;
  }

  .uploadBtn {
    position: absolute;
    //left: 585px;
    left: 688px;
    top: 5px;
    font-size: 20px;
    cursor: pointer;
    color: #999;

    &:hover {
      color: #000;
    }
  }
}
</style>
