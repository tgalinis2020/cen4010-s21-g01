(this.webpackJsonpclient=this.webpackJsonpclient||[]).push([[0],{85:function(t,e,n){"use strict";n.r(e);var a=n(0),r=n.n(a),s=n(32),c=n.n(s),i=n(8),o=n(14),u=n(21),l=n(64),j=n(11),d=n(38),b=n(58),h=n(29),p=n(49),O=n(12),m=n(13);var x=function(t,e){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,a="https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php".concat(e),r={method:t,headers:{"Content-Type":"application/json",Accept:"application/json"}};return["POST","PUT","PATCH","DELETE"].includes(t)&&(r.body=JSON.stringify({data:n})),fetch(a,r)},f=Object(a.createContext)(),v=n(10),g=n(9),y=n(3),C=n(20),w=n(27),k=n(57);var N=function(t){var e=t.split(" "),n=Object(i.a)(e,2),a=n[0],r=n[1],s=a.split("-"),c=Object(i.a)(s,3),o=c[0],u=c[1],l=c[2],j=r.split(":"),d=Object(i.a)(j,3),b=d[0],h=d[1],p=d[2];return new Date(Date.UTC(o,u-1,l,b,h,p))},E=["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],P=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];var T=function(t){var e=N(t);return"".concat(E[e.getDay()],", ").concat(P[e.getMonth()]," ").concat(e.getDate(),", ").concat(e.getFullYear())};var S=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:[],n=["include=author,tags","fields[users]=username","sort=-createdAt"].concat(Object(k.a)(e));return null!==t&&n.push("page[size]=".concat(t)),x("GET","/posts?".concat(n.join("&"))).then((function(t){return t.json()})).then((function(t){var e,n=t.data,a=t.included,r=[],s=Object(w.a)(n);try{var c=function(){var t=e.value,n=t.id,s=t.attributes,c=t.relationships,i={author:c.author.data.id,tags:"tags"in c?c.tags.data.map((function(t){return t.id})):[]};r.push({id:n,image:s.image,title:s.title,text:s.text,createdAt:T(s.createdAt),author:a.find((function(t){var e=t.type,n=t.id;return"users"===e&&n===i.author})).attributes.username,tags:a.filter((function(t){var e=t.type,n=t.id;return"tags"===e&&i.tags.includes(n)})).map((function(t){return t.attributes.text}))})};for(s.s();!(e=s.n()).done;)c()}catch(i){s.e(i)}finally{s.f()}return r}))};var I=function(t,e){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:void 0,a=!1,r=null;return function(){for(var s=this,c=arguments.length,i=new Array(c),o=0;o<c;o++)i[o]=arguments[o];var u=function(){e.apply(s,i),a=!1};a&&window.clearTimeout(r),"function"===typeof n&&n.apply(this,i)?u():(r=window.setTimeout(u,t),a=!0)}},A=n(1);var F=function(){var t=Object(a.useContext)(f),e=Object(i.a)(t,2),n=e[0],r=(e[1],Object(a.useState)([])),s=Object(i.a)(r,2),c=s[0],l=s[1],b=Object(a.useState)(!1),h=Object(i.a)(b,2),p=h[0],x=h[1],w=Object(o.g)(),k=I(1e3,(function(t){var e=t.target;if(""===e.value)p&&(x(!1),S(null).then(l));else{var n="filter[tags.text][in]=".concat(e.value.split(" ").join(","));x(!0),S(null,[n]).then(l)}}),(function(t){var e=t.key,n=t.target;return"Enter"===e||"Backspace"===e&&""===n.value}));return Object(a.useEffect)((function(){S(null===n?10:null).then(l)}),[]),Object(A.jsxs)(A.Fragment,{children:[n&&Object(A.jsxs)(y.a,{children:[Object(A.jsx)(y.a.Group,{children:Object(A.jsx)(y.a.Control,{type:"text",placeholder:"Search for posts by tag...",onChange:k})}),Object(A.jsx)(d.a,{className:"my-2",children:Object(A.jsxs)(j.a,{className:"ml-auto",onClick:function(){return w.push("/post")},children:[Object(A.jsx)(O.a,{className:"mr-2",icon:m.e}),"Create Post"]})})]}),Object(A.jsx)(v.a,{children:c.map((function(t,e){var n=t.id,a=t.image,r=t.title,s=t.author,c=t.text,i=t.createdAt,o=t.tags;return Object(A.jsx)(g.a,{xs:12,sm:6,md:4,children:Object(A.jsxs)(C.a,{className:"my-4",children:[Object(A.jsx)(u.b,{to:"/post/".concat(n),children:Object(A.jsx)(C.a.Img,{src:a})}),Object(A.jsxs)(C.a.Body,{children:[Object(A.jsx)(C.a.Title,{children:r}),Object(A.jsxs)(C.a.Text,{children:[Object(A.jsxs)("small",{className:"text-muted",children:["Posted by ",s," on ",i]}),Object(A.jsx)("p",{children:c}),o.length>0&&Object(A.jsxs)("p",{className:"text-muted",children:["Tags: ",o.join(", ")]})]})]})]})},e)}))}),0===c.length&&Object(A.jsx)("p",{children:"There are currently no posts."}),null===n&&Object(A.jsxs)("div",{className:"my-4 text-center",children:[Object(A.jsxs)("p",{children:["You must be logged in to see more posts. ",Object(A.jsx)(j.a,{variant:"primary",onClick:function(){return w.push("/signin")},children:"Sign in"})]}),Object(A.jsxs)("p",{children:["Don't have an account? ",Object(A.jsx)(j.a,{variant:"primary",onClick:function(){return w.push("/signup")},children:"Sign up"})]})]})]})};var G=function(){var t=Object(a.useContext)(f),e=Object(i.a)(t,1)[0],n=Object(a.useState)([]),r=Object(i.a)(n,2),s=r[0],c=r[1],o=Object(a.useState)(!1),l=Object(i.a)(o,2),j=l[0],d=l[1],b="filter[pets.subscribers]=".concat(e.user.id),h=I(1e3,(function(t){var e=t.target;if(""===e.value)j&&(d(!1),S(null,[b]).then(c));else{var n="filter[tags.text][in]=".concat(e.value.split(" ").join(","));d(!0),S(null,[b,n]).then(c)}}),(function(t){var e=t.key,n=t.target;return"Enter"===e||"Backspace"===e&&""===n.value}));return Object(a.useEffect)((function(){S(null,[b]).then(c)}),[]),Object(A.jsxs)(A.Fragment,{children:[e&&Object(A.jsx)(y.a,{children:Object(A.jsx)(y.a.Group,{children:Object(A.jsx)(y.a.Control,{type:"text",placeholder:"Search for posts by tag...",onChange:h})})}),Object(A.jsx)(v.a,{children:s.map((function(t,e){var n=t.id,a=t.image,r=t.title,s=t.author,c=t.text,i=t.createdAt,o=t.tags;return Object(A.jsx)(g.a,{xs:12,sm:6,md:4,children:Object(A.jsxs)(C.a,{className:"my-4",children:[Object(A.jsx)(u.b,{to:"/post/".concat(n),children:Object(A.jsx)(C.a.Img,{src:a})}),Object(A.jsxs)(C.a.Body,{children:[Object(A.jsx)(C.a.Title,{children:r}),Object(A.jsxs)(C.a.Text,{children:[Object(A.jsxs)("small",{className:"text-muted",children:["Posted by ",s," on ",i]}),Object(A.jsx)("p",{children:c}),o.length>0&&Object(A.jsxs)("p",{className:"text-muted",children:["Tags: ",o.join(", ")]})]})]})]})},e)}))}),0===s.length&&Object(A.jsx)("p",{children:"You have no subscriptions!"})]})};var L=function(){return Object(A.jsx)("h1",{children:"Favorites"})};var R=function(){var t=Object(o.g)();return Object(A.jsx)(O.a,{style:{cursor:"pointer"},className:"mr-3",icon:m.a,size:"1x",onClick:function(){return t.replace("/dashboard")}})},U=n(19);var D=function(t){var e=new FormData;return e.append("data",t,t.name),fetch("https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php/upload",{method:"POST",body:e})},B=n(46);var M=function(t){for(var e={},n=0,r=Object.keys(t);n<r.length;n++){e[r[n]]={value:"",dirty:!1,error:null}}var s=Object(a.useState)(e),c=Object(i.a)(s,2),o=c[0],u=c[1],l=function(t){return o[t].value},j=function(e,n){return t[e].reduce((function(t,e){return t.then((function(t){return null!==t&&void 0!==t?t:e(n,l)}))}),Promise.resolve(null)).then((function(t){return u((function(a){return Object(U.a)(Object(U.a)({},a),{},Object(B.a)({},e,{value:n,dirty:!0,error:t}))})),null===t})).catch((function(){return u((function(t){return Object(U.a)(Object(U.a)({},t),{},Object(B.a)({},e,{value:n,dirty:!0,error:"Invalid value."}))})),!1}))};return{get:l,set:function(t){return I(500,(function(e){var n=e.target;return j(t,n.value)}),(function(t){var e=t.key,n=t.target;return"Enter"===e||"Backspace"===e&&""===n.value}))},isInvalid:function(t){return o[t].dirty&&null!==o[t].error},getError:function(t){return o[t].error},getValidity:function(){return Promise.all(Object.keys(o).map((function(t){return j(t,o[t].value)}))).then((function(t){return t.reduce((function(t,e){return t&&e}),!0)}))}}};var z=function(){var t=Object(a.useContext)(f),e=Object(i.a)(t,2),n=e[0],r=e[1],s=Object(a.useState)(null),c=Object(i.a)(s,2),o=c[0],u=c[1],l=function(t){return function(e){return Promise.resolve(""===e?"".concat(t," cannot be empty."):null)}},d=M({password:[l("Password")],newPassword:[l("New password"),function(t,e){return Promise.resolve(t===e("password")?"Passwords should not match":null)}]});return Object(A.jsxs)(A.Fragment,{children:[Object(A.jsxs)(y.a,{noValidate:!0,children:[Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Current Password"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.Control,{isInvalid:d.isInvalid("password"),type:"password",placeholder:"Current password",onChange:d.set("password")}),d.isInvalid("password")&&Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:d.getError("password")})]})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"New Password"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.Control,{isInvalid:d.isInvalid("newPassword"),type:"password",placeholder:"New password",onChange:d.set("newPassword")}),d.isInvalid("newPassword")&&Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:d.getError("newPassword")})]})]}),Object(A.jsx)(y.a.Group,{as:v.a,children:Object(A.jsx)(g.a,{sm:{span:10,offset:2},children:Object(A.jsx)(j.a,{variant:"primary",onClick:function(){return n.user.updatePassword(d.get("password"),d.get("newPassword")).then((function(t){window.alert(204===t.status?"Password updated!":"An error occured while attempting to update your password.")}))},children:"Update Password"})})})]}),Object(A.jsx)("hr",{}),Object(A.jsxs)(y.a,{noValidate:!0,children:[Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Avatar"}),Object(A.jsx)(g.a,{sm:10,children:Object(A.jsx)(y.a.File,{custom:!0,label:o?o.name:"Upload an image",onChange:function(t){var e=t.target;return u(e.files.item(0))}})})]}),Object(A.jsx)(y.a.Group,{as:v.a,children:Object(A.jsx)(g.a,{sm:{span:10,offset:2},children:Object(A.jsx)(j.a,{variant:"primary",onClick:function(){return D(o).then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){return n.user.setAttribute("avatar",t)})).then((function(t){return t.update()})).then((function(t){return r((function(e){return Object(U.a)(Object(U.a)({},e),{},{user:t})}))})).then((function(){return window.alert("Avatar updated!")}))},children:"Update Avatar"})})})]})]})},V=n(24),J=n(43);var Y=function(t){return x("GET","/users/".concat(t,"/pets")).then((function(t){return t.json()})).then((function(t){return t.data.map((function(t){var e=t.id,n=t.attributes;return{id:e,name:n.name,avatar:n.avatar}}))}))};var _=function(){var t=Object(a.useContext)(f),e=Object(i.a)(t,1)[0],n=Object(a.useState)(null),r=Object(i.a)(n,2),s=r[0],c=r[1],o=Object(a.useState)([]),u=Object(i.a)(o,2),l=u[0],d=u[1],b=Object(a.useRef)(null),h=Object(a.useRef)(null),p=M({petName:[function(t){return Promise.resolve(""===t?"Pet nane cannot be empty.":null)},function(t){return Promise.resolve(l.includes(t)?'You already have a pet named "'.concat(t,'."'):null)}]}),C=function(){return(null===s?Promise.resolve(null):D(s).then((function(t){return t.json()})).then((function(t){return t.data}))).then((function(t){return console.log(t),x("POST","/pets",(n=t,{type:"pets",attributes:{name:p.get("petName"),avatar:n},relationships:{owner:{data:{type:"users",id:e.user.id}}}}));var n})).then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){var e=t.id,n=t.attributes;c(null),d((function(t){return[].concat(Object(k.a)(t),[{id:e,name:n.name,avatar:n.avatar}])})),b.current.value="",h.current.value=""}))};return Object(a.useEffect)((function(){Y(e.user.id).then(d)}),[]),Object(A.jsxs)(A.Fragment,{children:[Object(A.jsxs)(y.a,{noValidate:!0,children:[Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Pet Name"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.Control,{ref:h,isInvalid:p.isInvalid("petName"),type:"text",placeholder:"Enter you pet's name",onChange:p.set("petName")}),p.isInvalid("petName")&&Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:p.getError("petName")})]})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Pet Avatar"}),Object(A.jsx)(g.a,{sm:10,children:Object(A.jsx)(y.a.File,{ref:b,custom:!0,label:null===s?"Upload an image":s.name,onChange:function(t){var e=t.target;return c(e.files.item(0))}})})]}),Object(A.jsx)(y.a.Group,{as:v.a,children:Object(A.jsx)(g.a,{sm:{span:10,offset:2},children:Object(A.jsx)(j.a,{variant:"primary",onClick:function(){return p.getValidity().then((function(t){if(t)return C()}))},children:"Add Pet"})})})]}),Object(A.jsx)("hr",{}),Object(A.jsx)("h3",{children:"Pets"}),l.length>0?Object(A.jsx)(J.a,{children:l.map((function(t,e){return Object(A.jsx)(J.a.Item,{children:Object(A.jsxs)(V.a,{children:[null===t.avatar?Object(A.jsx)(O.a,{icon:m.d,size:"4x",className:"d-block mr-3"}):Object(A.jsx)("img",{style:{width:"64px",height:"64px",borderRadius:"50%"},src:t.avatar,className:"mr-3"}),Object(A.jsx)(V.a.Body,{className:"d-flex align-self-center",children:t.name})]})},e)}))}):Object(A.jsx)("p",{children:"You have no pets!"})]})};var H=function(){var t=Object(o.i)(),e=t.url,n=t.path,r=Object(o.g)(),s=Object(a.useState)("account"),c=Object(i.a)(s,2),u=c[0],l=c[1],b=function(t){return function(){l(t),r.replace("".concat(e,"/").concat(t))}};return Object(A.jsxs)(A.Fragment,{children:[Object(A.jsxs)("h1",{children:[Object(A.jsx)(R,{}),"Settings"]}),Object(A.jsx)(d.a,{className:"d-flex my-4",children:["account","pets"].map((function(t,e){return Object(A.jsx)(j.a,{variant:t===u?"primary":"secondary",onClick:b(t),children:"".concat(t.charAt(0).toUpperCase()).concat(t.substr(1))},e)}))}),Object(A.jsxs)(o.d,{children:[Object(A.jsx)(o.b,{path:"".concat(n,"/account"),children:Object(A.jsx)(z,{})}),Object(A.jsx)(o.b,{path:"".concat(n,"/pets"),children:Object(A.jsx)(_,{})}),Object(A.jsx)(o.b,{path:"".concat(n,"/subscriptions"),children:Object(A.jsx)("p",{children:"Manage Subscriptions"})}),Object(A.jsx)(o.b,{exact:!0,path:"".concat(n),children:Object(A.jsx)(o.a,{to:"".concat(e,"/account")})})]})]})};var q=function(t){return function(e){if(e.status!==t)throw e.status;return e.json()}},W=n(30),Z=n(31),$=n(42),K=n(41),Q=function(){function t(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};Object(W.a)(this,t),this.id=e.id||null,this.attributes=e.attributes||{},this.dirtyAttributes=[],this.relationships=e.relationships||{}}return Object(Z.a)(t,[{key:"type",get:function(){return"generic"}},{key:"hydrate",value:function(t){return this.id=t.id||null,this.attributes=t.attributes||{},this.relationships=t.relationships||{},this}},{key:"setAttribute",value:function(t,e){return this.dirtyAttributes.push(t),this.attributes[t]=e,this}},{key:"getAttribute",value:function(t){return this.attributes[t]}},{key:"toResourceIdentifier",value:function(){return{type:this.type,id:this.id}}},{key:"toResource",value:function(){return{type:this.type,id:this.id,attributes:this.attributes}}},{key:"create",value:function(){var t,e=this,n=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,a={},r=Object(w.a)(this.dirtyAttributes);try{for(r.s();!(t=r.n()).done;){var s=t.value;a[s]=this.attributes[s]}}catch(i){r.e(i)}finally{r.f()}var c={type:this.type,attributes:a};return null!==n&&(c.relationships=n),x("POST","/".concat(this.type),c).then((function(t){return e.dirtyAttributes=[],t.json()})).then((function(t){return t.data}))}},{key:"update",value:function(){var t,e=this,n={},a=Object(w.a)(this.dirtyAttributes);try{for(a.s();!(t=a.n()).done;){var r=t.value;n[r]=this.attributes[r]}}catch(c){a.e(c)}finally{a.f()}var s={type:this.type,id:this.id,attributes:n};return x("PATCH","/".concat(this.type,"/").concat(this.id),s).then((function(){return e.dirtyAttributes=[],e}))}},{key:"delete",value:function(){return x("DELETE","/".concat(this.type,"/").concat(this.id))}},{key:"updateRelationship",value:function(t,e,n){return x(t,"/".concat(this.type,"/").concat(this.id,"/relationships/").concat(e),n)}},{key:"updateToManyRelationship",value:function(t,e,n){return this.updateRelationship(t,e,n.map((function(t){return t.toResourceIdentifier()})))}},{key:"updateToOneRelationship",value:function(t,e,n){return this.updateRelationship(t,e,n.toResourceIdentifier())}}]),t}(),X=function(t){Object($.a)(n,t);var e=Object(K.a)(n);function n(){return Object(W.a)(this,n),e.apply(this,arguments)}return Object(Z.a)(n,[{key:"type",get:function(){return"users"}},{key:"create",value:function(t){var e,n=this,a=this.type,r={},s=Object(w.a)(this.dirtyAttributes);try{for(s.s();!(e=s.n()).done;){var c=e.value;r[c]=this.attributes[c]}}catch(i){s.e(i)}finally{s.f()}return x("POST","/".concat(a),{type:a,attributes:r}).then((function(t){return t.json()})).then((function(e){var a=e.data;return n.hydrate(a),x("PUT","/passwords/".concat(a.id),t)})).then((function(t){return n}))}},{key:"updatePassword",value:function(t,e){return x("PATCH","/passwords/".concat(this.id),{current:t,new:e})}},{key:"login",value:function(t){return x("POST","/session",{username:this.getAttribute("username"),password:t})}},{key:"logout",value:function(){return x("DELETE","/session")}},{key:"subscribeTo",value:function(t){return this.updateToManyRelationship("POST","subscriptions",[t])}},{key:"unsubscribeFrom",value:function(t){return this.updateToManyRelationship("DELETE","subscriptions",[t])}},{key:"addFavorite",value:function(t){return this.updateToManyRelationship("POST","favorites",[t])}},{key:"removeFavorite",value:function(t){return this.updateToManyRelationship("DELETE","favorites",[t])}},{key:"like",value:function(t){return this.updateToManyRelationship("POST","liked-posts",[t])}},{key:"unlike",value:function(t){return this.updateToManyRelationship("DELETE","liked-posts",[t])}}]),n}(Q);var tt=function(){var t=Object(a.useContext)(f),e=Object(i.a)(t,2),n=(e[0],e[1]),r=Object(a.useState)(""),s=Object(i.a)(r,2),c=s[0],u=s[1],l=Object(a.useState)(""),d=Object(i.a)(l,2),b=d[0],h=d[1],p=Object(o.g)(),O=function(t){return I(500,(function(e){var n=e.target;return t(n.value)}),(function(t){var e=t.key,n=t.target;return"Enter"===e||"Backspace"===e&&""===n.value}))};return Object(A.jsxs)(A.Fragment,{children:[Object(A.jsxs)("h1",{className:"mb-4",children:[Object(A.jsx)(R,{}),"Sign In"]}),Object(A.jsxs)(y.a,{children:[Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Username"}),Object(A.jsx)(g.a,{sm:10,children:Object(A.jsx)(y.a.Control,{type:"text",placeholder:"Enter username",onChange:O(u)})})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Password"}),Object(A.jsx)(g.a,{sm:10,children:Object(A.jsx)(y.a.Control,{type:"password",placeholder:"Enter password",onChange:O(h)})})]}),Object(A.jsx)(y.a.Group,{as:v.a,children:Object(A.jsx)(g.a,{sm:{span:10,offset:2},children:Object(A.jsx)(j.a,{variant:"primary",onClick:function(){return x("POST","/session",{username:c,password:b}).then(q(201)).then((function(t){return t.data})).then((function(t){var e=t.uid;return x("GET","/users/".concat(e,"?include=subscriptions"))})).then((function(t){return t.json()})).then((function(t){var e=t.data,n=t.included;return{user:new X(e),subscriptions:n.map((function(t){return t.id}))}})).then(n).then((function(){return p.replace("/dashboard")})).catch((function(t){console.error(t),window.alert("Invalid username/password combination!")}))},children:"Sign In"})})})]})]})};var et=function(){var t=Object(a.useState)(null),e=Object(i.a)(t,2),n=e[0],r=e[1],s=Object(a.useContext)(f),c=Object(i.a)(s,2),u=(c[0],c[1]),l=Object(o.g)(),d=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"Value";return function(e){return Promise.resolve(""===e?"".concat(t," cannot be empty."):null)}},b=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;return function(n){return x("GET","/users?filter[".concat(t,"]=").concat(n)).then((function(t){return t.json()})).then((function(n){return"undefined"===typeof n.data.pop()?null:"Provided ".concat(e||t," is already in use.")}))}},h=M({username:[d("Username"),b("username")],password:[d("Password")],firstName:[d("First name")],lastName:[d("Last name")],email:[d("E-mail address"),b("email","e-mail address")]}),p=new X;return Object(A.jsxs)(A.Fragment,{children:[Object(A.jsxs)("h1",{className:"mb-4",children:[Object(A.jsx)(R,{}),"Sign Up"]}),Object(A.jsxs)(y.a,{noValidate:!0,children:[Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"First Name"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.Control,{isInvalid:h.isInvalid("firstName"),type:"text",placeholder:"Enter first name",onChange:h.set("firstName")}),h.isInvalid("firstName")&&Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:h.getError("firstName")})]})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Last Name"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.Control,{isInvalid:h.isInvalid("lastName"),type:"text",placeholder:"Enter last name",onChange:h.set("lastName")}),h.isInvalid("lastName")&&Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:h.getError("lastName")})]})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"E-mail Address"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.Control,{isInvalid:h.isInvalid("email"),type:"text",placeholder:"Enter e-mail address",onChange:h.set("email")}),h.isInvalid("email")&&Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:h.getError("email")})]})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Username"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.Control,{isInvalid:h.isInvalid("username"),type:"text",placeholder:"Enter username",onChange:h.set("username")}),h.isInvalid("username")&&Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:h.getError("username")})]})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Password"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.Control,{isInvalid:h.isInvalid("password"),type:"password",placeholder:"Enter password",onChange:h.set("password")}),h.isInvalid("password")&&Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:h.getError("password")})]})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Avatar"}),Object(A.jsx)(g.a,{sm:10,children:Object(A.jsx)(y.a.File,{custom:!0,label:"Upload an image",onChange:function(t){var e=t.target;return r(e.files.item(0))}})})]}),Object(A.jsx)(y.a.Group,{as:v.a,children:Object(A.jsx)(g.a,{sm:{span:10,offset:2},children:Object(A.jsx)(j.a,{variant:"primary",onClick:function(){return h.getValidity().then((function(t){return t&&p.setAttribute("firstName",h.get("firstName")).setAttribute("lastName",h.get("lastName")).setAttribute("email",h.get("email")).setAttribute("username",h.get("username")).create(h.get("password")).then((function(){var t=x("POST","/session",{username:h.get("username"),password:h.get("password")});return null!==n&&t.then((function(){return D(n)})).then((function(t){return t.json()})).then((function(t){var e=t.data;return p.setAttribute("avatar",e)})).then((function(t){return t.update()})),t})).then((function(){return u({user:p,subscriptions:[]})})).then((function(){return l.replace("/dashboard")})).catch(console.log)}))},children:"Sign Up"})})})]})]})};var nt=function(t){var e=t.post,n=t.session,r=t.onSubmitted,s=Object(a.useState)(""),c=Object(i.a)(s,2),o=c[0],u=c[1],l=Object(a.useRef)(null);return Object(A.jsxs)(y.a,{children:[Object(A.jsx)(y.a.Group,{children:Object(A.jsx)(y.a.Control,{as:"textarea",ref:l,onChange:function(t){var e=t.target;return u(e.value)}})}),Object(A.jsx)(y.a.Group,{children:Object(A.jsxs)(j.a,{variant:"primary",onClick:function(){return x("POST","/comments",{type:"comments",attributes:{text:o},relationships:{author:{data:{type:"users",id:n.user.id}},post:{data:{type:"posts",id:e.id}}}}).then((function(t){return t.json()})).then((function(t){return t.data})).then(r).then((function(){return u("")})).then((function(){l.current.value=""})).catch(console.log)},children:[Object(A.jsx)(O.a,{className:"mr-2",icon:m.e}),"Add Comment"]})})]})};var at=function(t){var e=t.text,n=t.createdAt,a=t.author,r=a.avatar?Object(A.jsx)("img",{style:{width:"64px",height:"64px",borderRadius:"50%",border:"1px solid #ccc"},className:"mr-3",src:a.avatar,alt:"".concat(a.username,"'s profile picture")}):Object(A.jsx)(O.a,{className:"mr-3",size:"4x",icon:m.j});return Object(A.jsxs)(V.a,{className:"mb-4",children:[r,Object(A.jsxs)(V.a.Body,{children:[Object(A.jsxs)("small",{className:"text-muted",children:["Posted by ",a.username," on ",n]}),Object(A.jsx)("p",{children:e})]})]})};var rt=function(t){return x("GET","/posts/".concat(t,"/comments?").concat(["include=author","fields[users]=username,avatar","sort=-createdAt"].join("&"))).then((function(t){return t.json()})).then((function(t){var e=t.data,n=t.included;return e.map((function(t){var e=t.id,a=t.attributes,r=t.relationships;return{id:e,text:a.text,createdAt:T(a.createdAt),author:n.find((function(t){return t.id===r.author.data.id})).attributes}}))}))};var st=function(t){return x("GET","/posts/".concat(t,"?").concat(["include=author,tags,pets","fields[users]=username,avatar","fields[pets]=name,avatar"].join("&"))).then((function(t){return t.json()})).then((function(t){for(var e=t.data,n=t.included,a=e.id,r=e.attributes,s=e.relationships,c=r.image,i=r.title,o=r.text,u=r.createdAt,l={author:s.author.data.id},j=0,d=["tags","pets","likes"];j<d.length;j++){var b=d[j];l[b]=b in s?s[b].data.map((function(t){return t.id})):[]}return{id:a,image:c,title:i,text:o,createdAt:T(u),author:n.find((function(t){var e=t.type,n=t.id;return"users"===e&&n===l.author})).attributes,tags:n.filter((function(t){var e=t.type,n=t.id;return"tags"===e&&l.tags.includes(n)})).map((function(t){return t.attributes.text})),pets:n.filter((function(t){var e=t.type,n=t.id;return"pets"===e&&l.pets.includes(n)})).map((function(t){var e=t.id,n=t.attributes;return{id:e,name:n.name,avatar:n.avatar}}))}}))};var ct=function(){var t=Object(a.useContext)(f),e=Object(i.a)(t,2),n=e[0],r=e[1],s=Object(a.useState)(null),c=Object(i.a)(s,2),u=c[0],l=c[1],d=Object(a.useState)([]),b=Object(i.a)(d,2),h=b[0],p=b[1],v=Object(o.h)().id,g=function(t){return[{type:"pets",id:t}]},y=function(t){return function(){x("POST","/users/".concat(n.user.id,"/relationships/subscriptions"),g(t)).then((function(e){204!==e.status&&console.error("Subscription was not created! Bug Tom about this! >:("),r((function(e){return Object(U.a)(Object(U.a)({},e),{},{subscriptions:e.subscriptions.concat([t])})}))}))}};return Object(a.useEffect)((function(){st(v).then(l).then((function(){return rt(v)})).then(p)}),[]),Object(A.jsx)(A.Fragment,{children:u?Object(A.jsxs)(A.Fragment,{children:[Object(A.jsxs)("h1",{children:[Object(A.jsx)(R,{}),u.title]}),Object(A.jsxs)(C.a,{className:"my-4",children:[Object(A.jsx)(C.a.Img,{src:u.image}),Object(A.jsx)(C.a.Body,{children:Object(A.jsxs)(C.a.Text,{children:[Object(A.jsxs)(V.a,{className:"mb-4",children:[u.author.avatar?Object(A.jsx)("img",{style:{width:"64px",height:"64px",borderRadius:"50%",border:"1px solid #ccc"},className:"mr-3",src:u.author.avatar,alt:"".concat(u.author.username,"'s profile picture")}):Object(A.jsx)(O.a,{className:"mr-3",size:"4x",icon:m.j}),Object(A.jsxs)(V.a.Body,{children:[Object(A.jsxs)("small",{className:"text-muted",children:["Posted by ",u.author.username," on ",u.createdAt]}),Object(A.jsx)("p",{children:u.text})]})]}),u.pets.length>0&&Object(A.jsxs)("div",{className:"my-3",children:[Object(A.jsxs)("p",{className:"text-muted",children:[u.author.username,"'s pets in this post:"]}),Object(A.jsx)(J.a,{children:u.pets.map((function(t,e){return Object(A.jsx)(J.a.Item,{children:Object(A.jsxs)(V.a,{children:[null===t.avatar?Object(A.jsx)(O.a,{icon:m.d,size:"4x",className:"d-block mr-3"}):Object(A.jsx)("img",{style:{width:"64px",height:"64px",borderRadius:"50%"},src:t.avatar,className:"mr-3"}),Object(A.jsxs)(V.a.Body,{className:"d-flex align-self-center",children:[t.name,n&&Object(A.jsx)(A.Fragment,{children:n.subscriptions.includes(t.id)?Object(A.jsx)(j.a,{className:"ml-auto",variant:"danger",onClick:(a=t.id,function(){x("DELETE","/users/".concat(n.user.id,"/relationships/subscriptions"),g(a)).then((function(t){204!==t.status&&console.error("Subscription was not removed! Bug Tom about this! >:("),r((function(t){return Object(U.a)(Object(U.a)({},t),{},{subscriptions:t.subscriptions.filter((function(t){return t!==a}))})}))}))}),children:"Unsubscribe"}):Object(A.jsx)(j.a,{className:"ml-auto",variant:"success",onClick:y(t.id),children:"Subscribe"})})]})]})},e);var a}))})]}),u.tags.length>0&&Object(A.jsxs)("p",{className:"text-muted",children:["Tags: ",u.tags.join(", ")]})]})})]}),n&&Object(A.jsx)(nt,{session:n,post:u,onSubmitted:rt}),Object(A.jsx)("hr",{}),Object(A.jsxs)("h3",{className:"mb-4",children:["Comments (",h.length,")"]}),h.length>0?h.map((function(t,e){return Object(A.jsx)(at,Object(U.a)({},t),e)})):Object(A.jsx)("p",{children:"No comments available."})]}):Object(A.jsx)("p",{className:"text-center my-4",children:Object(A.jsx)(O.a,{icon:m.h,size:"3x",pulse:!0})})})},it=n(66),ot=n(36),ut=function(t){Object($.a)(n,t);var e=Object(K.a)(n);function n(){return Object(W.a)(this,n),e.apply(this,arguments)}return Object(Z.a)(n,[{key:"type",get:function(){return"tags"}}]),n}(Q),lt=function(t){Object($.a)(n,t);var e=Object(K.a)(n);function n(){return Object(W.a)(this,n),e.apply(this,arguments)}return Object(Z.a)(n,[{key:"type",get:function(){return"posts"}},{key:"create",value:function(){var t,e=this,a=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,r=arguments.length>1&&void 0!==arguments[1]?arguments[1]:[],s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:[],c={},i=Object(w.a)(r);try{for(i.s();!(t=i.n()).done;){var o=t.value;c[o]=!1}}catch(u){i.e(u)}finally{i.f()}return x("GET","/tags?filter[text][in]=".concat(r.join(","))).then((function(t){return t.json()})).then((function(t){var e,n=t.data,a=[],s=Object(w.a)(n);try{for(s.s();!(e=s.n()).done;){var i=e.value,o=new ut(i);c[o.getAttribute("text")]=!0,a.push(o)}}catch(u){s.e(u)}finally{s.f()}var l=r.filter((function(t){return!1===c[t]}));return l.length>0?Promise.all(l.map((function(t){return x("POST","/tags",{type:"tags",attributes:{text:t}})}))).then((function(t){return Promise.all(t.map((function(t){return t.json()})))})).then((function(t){return t.map((function(t){var e=t.data;return new ut(e)}))})).then((function(t){return t.concat(a)})):a})).then((function(t){var e={},n=0;return null!==a&&(e.author={data:a.toResourceIdentifier()},n++),s.length>0&&(e.pets={data:s.map((function(t){return{type:"pets",id:t}}))},n++),t.length>0&&(e.tags={data:t.map((function(t){return t.toResourceIdentifier()}))},n++),n>0?e:null})).then((function(t){return Object(it.a)(Object(ot.a)(n.prototype),"create",e).call(e,t)})).then((function(t){return e.hydrate(t)}))}}]),n}(Q);var jt=function(){var t,e=Object(a.useState)(null),n=Object(i.a)(e,2),r=n[0],s=n[1],c=Object(a.useState)([]),u=Object(i.a)(c,2),l=u[0],d=u[1],b=Object(a.useState)(!0),h=Object(i.a)(b,2),p=h[0],x=h[1],C=Object(a.useContext)(f),w=Object(i.a)(C,1)[0],k=Object(o.g)(),N=M({title:[(t=10,function(e){return Promise.resolve(e.length<t?"Post title must be ".concat(t," or more characters long."):null)}),function(t){return function(e){return Promise.resolve(e.length>t?"Post title length cannot exceed ".concat(t," characters."):null)}}(35)],text:[],tags:[function(t){return Promise.resolve(/^[A-Za-z ]*$/.test(t)?null:"Each tag must be a word separated by a space.")}]});return Object(a.useEffect)((function(){Y(w.user.id).then((function(t){x(!1),d(t.map((function(t){return Object(U.a)(Object(U.a)({},t),{},{isChecked:!1})})))}))}),[]),Object(A.jsxs)(A.Fragment,{children:[Object(A.jsxs)("h1",{className:"mb-4",children:[Object(A.jsx)(R,{}),"Create Post"]}),Object(A.jsxs)(y.a,{children:[Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Title"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.Control,{type:"text",isInvalid:N.isInvalid("title"),placeholder:"Enter post title",onChange:N.set("title")}),N.isInvalid("title")&&Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:N.getError("title")})]})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Image"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.File,{custom:!0,label:r?r.name:"Upload in image",onChange:function(t){var e=t.target;return s(e.files.item(0))}}),Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:"A post image is required."})]})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Caption"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.Control,{as:"textarea",isInvalid:N.isInvalid("text"),placeholder:"Enter post caption",onChange:N.set("text")}),N.isInvalid("text")&&Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:N.getError("text")})]})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Tags"}),Object(A.jsxs)(g.a,{sm:10,children:[Object(A.jsx)(y.a.Control,{type:"text",isInvalid:N.isInvalid("tags"),placeholder:"Enter tags separated by a space",onChange:N.set("tags")}),N.isInvalid("tags")&&Object(A.jsx)(y.a.Control.Feedback,{type:"invalid",children:N.getError("tags")})]})]}),Object(A.jsxs)(y.a.Group,{as:v.a,children:[Object(A.jsx)(y.a.Label,{column:!0,sm:2,children:"Pets in this post"}),Object(A.jsxs)(g.a,{sm:10,children:[p&&Object(A.jsx)(O.a,{className:"my-3",icon:m.h,pulse:!0}),!p&&l.map((function(t,e){return Object(A.jsx)(y.a.Check,{type:"checkbox",label:t.name,defaultChecked:t.isChecked,onChange:(n=e,function(){return d((function(t){return t[n].isChecked=!t[n].isChecked,t}))})},e);var n})),!p&&0===l.length&&Object(A.jsx)(y.a.Control,{plaintext:!0,readOnly:!0,defaultValue:"You have no pets!"})]})]}),Object(A.jsx)(y.a.Group,{as:v.a,children:Object(A.jsx)(g.a,{sm:{span:10,offset:2},children:Object(A.jsxs)(j.a,{variant:"primary",onClick:function(){return N.getValidity().then((function(t){return t&&function(){if(null!==r){var t=N.get("tags").split(" ").map((function(t){return t.trim().toLowerCase()})).filter((function(t){return t.length>0})),e=l.filter((function(t){return t.isChecked})).map((function(t){return t.id}));return D(r).then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){return(new lt).setAttribute("image",t).setAttribute("title",N.get("title")).setAttribute("text",N.get("text"))})).then((function(n){return n.create(w.user,t,e)})).then((function(t){return k.replace("/post/".concat(t.id))}))}window.alert("Posts need an image!")}()}))},children:[Object(A.jsx)(O.a,{icon:m.e,className:"mr-2"}),"Create Post"]})})})]})]})};var dt=function(){return x("GET","/session").then((function(t){return t.json()})).then((function(t){return t.data.uid})).then((function(t){return x("GET","/users/".concat(t,"?include=subscriptions"))})).then((function(t){return t.json()})).then((function(t){var e=t.data,n=t.included;return{user:new X(e),subscriptions:n.map((function(t){return t.id}))}}))};function bt(){var t=Object(o.g)();return Object(A.jsx)(b.a,{className:"ml-auto",children:Object(A.jsxs)(h.a,{title:Object(A.jsx)(O.a,{icon:m.j,size:"2x"}),children:[Object(A.jsxs)(h.a.Item,{onClick:function(){return t.push("/signin")},children:[Object(A.jsx)(O.a,{className:"mr-2",icon:m.f})," Sign In"]}),Object(A.jsxs)(h.a.Item,{onClick:function(){return t.push("/signup")},children:[Object(A.jsx)(O.a,{className:"mr-2",icon:m.i})," Sign Up"]})]})})}function ht(){var t=Object(a.useContext)(f),e=Object(i.a)(t,2),n=e[0],r=e[1],s=Object(o.g)(),c=null===n.user.getAttribute("avatar")?Object(A.jsx)(O.a,{icon:m.j,size:"2x"}):Object(A.jsx)("img",{style:{borderRadius:"50%",border:"1px solid #888",width:"48px",height:"48px"},src:n.user.getAttribute("avatar")});return Object(A.jsx)(b.a,{className:"ml-auto",children:Object(A.jsxs)(h.a,{className:"text-center",title:c,children:[Object(A.jsx)(h.a.ItemText,{className:"text-center",children:n.user.getAttribute("username")}),Object(A.jsx)(h.a.Divider,{}),Object(A.jsxs)(h.a.Item,{as:u.b,to:"/settings",children:[Object(A.jsx)(O.a,{className:"mr-2",icon:m.c}),"Settings"]}),Object(A.jsxs)(h.a.Item,{onClick:function(){return x("DELETE","/session").then((function(){return r(null)})).then((function(){return s.push("/")}))},children:[Object(A.jsx)(O.a,{className:"mr-2",icon:m.g}),"Sign Out"]})]})})}function pt(){var t=Object(o.i)().url,e=Object(o.g)(),n=e.location.pathname.split("/").pop(),r=["explore","subscriptions"],s=Object(a.useState)(r.includes(n)?n:"explore"),c=Object(i.a)(s,2),u=c[0],l=c[1],b=function(n){return function(){l(n),e.replace("".concat(t,"/").concat(n))}};return Object(A.jsx)(d.a,{className:"d-flex my-4",children:r.map((function(t,e){return Object(A.jsx)(j.a,{variant:t===u?"primary":"secondary",onClick:b(t),children:"".concat(t.charAt(0).toUpperCase()).concat(t.substr(1))},e)}))})}function Ot(){var t=Object(a.useContext)(f),e=Object(i.a)(t,1)[0],n=Object(o.i)().url;return Object(A.jsxs)(A.Fragment,{children:[e?Object(A.jsx)(pt,{}):null,Object(A.jsxs)(o.d,{children:[Object(A.jsx)(o.b,{path:"".concat(n,"/explore"),children:Object(A.jsx)(F,{})}),Object(A.jsx)(o.b,{path:"".concat(n,"/subscriptions"),children:Object(A.jsx)(G,{})}),Object(A.jsx)(o.b,{path:"".concat(n,"/favorites"),children:Object(A.jsx)(L,{})}),Object(A.jsx)(o.b,{path:"".concat(n,"/"),children:Object(A.jsx)(o.a,{to:"".concat(n,"/explore")})})]})]})}var mt=function(t){var e=t.title,n=Object(a.useState)(null),r=Object(i.a)(n,2),s=r[0],c=r[1];return Object(a.useEffect)((function(){dt().then(c).catch((function(){return console.log("Not logged in")}))}),[]),Object(A.jsx)(f.Provider,{value:n,children:Object(A.jsxs)(u.a,{children:[Object(A.jsx)(p.a,{className:"mb-4",bg:"dark",variant:"dark",expand:"lg",children:Object(A.jsxs)(l.a,{children:[Object(A.jsxs)(p.a.Brand,{as:u.b,to:"/",children:[e,Object(A.jsx)(O.a,{className:"ml-2",icon:m.b})]}),Object(A.jsx)(p.a.Toggle,{"aria-controls":"main-nav"}),Object(A.jsx)(p.a.Collapse,{id:"main-nav",children:s?Object(A.jsx)(ht,{}):Object(A.jsx)(bt,{})})]})}),Object(A.jsx)(l.a,{children:Object(A.jsxs)(o.d,{children:[Object(A.jsx)(o.b,{path:"/dashboard",children:Object(A.jsx)(Ot,{})}),Object(A.jsx)(o.b,{path:"/post/:id",children:Object(A.jsx)(ct,{})}),Object(A.jsx)(o.b,{path:"/post",children:Object(A.jsx)(jt,{})}),Object(A.jsx)(o.b,{path:"/signin",children:Object(A.jsx)(tt,{})}),Object(A.jsx)(o.b,{path:"/signup",children:Object(A.jsx)(et,{})}),Object(A.jsx)(o.b,{path:"/settings",children:Object(A.jsx)(H,{})}),Object(A.jsx)(o.b,{exact:!0,path:"/",children:Object(A.jsx)(o.a,{to:"/dashboard"})})]})})]})})};n(84);c.a.render(Object(A.jsx)(r.a.StrictMode,{children:Object(A.jsx)(mt,{title:"The Pet Park"})}),document.getElementById("root"))}},[[85,1,2]]]);
//# sourceMappingURL=main.d76ee66c.chunk.js.map