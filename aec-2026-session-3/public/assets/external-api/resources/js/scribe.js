import _ from 'lodash';
window._ = _;

import hljs from 'highlight.js';
import 'highlight.js/styles/docco.css';

window.hljs = hljs;
hljs.highlightAll();

import { CodeJar } from 'codejar';
window.CodeJar = CodeJar;
