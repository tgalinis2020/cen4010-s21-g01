(this.webpackJsonpclient=this.webpackJsonpclient||[]).push([[0],{87:function(t,e,n){"use strict";n.r(e);var a=n(0),r=n.n(a),s=n(34),c=n.n(s),i=n(8),u=n(14),o=n(24),l=n(66),j=n(11),d=n(31),h=n(59),b=n(30),p=n(50),O=n(12),m=n(13);var x=function(t,e){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,a="https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php".concat(e),r={method:t,headers:{"Content-Type":"application/json",Accept:"application/json"}};return["POST","PUT","PATCH"].includes(t)&&(r.body=JSON.stringify({data:n})),fetch(a,r)};var f=function(t){var e=t.split(" "),n=Object(i.a)(e,2),a=n[0],r=n[1],s=a.split("-"),c=Object(i.a)(s,3),u=c[0],o=c[1],l=c[2],j=r.split(":"),d=Object(i.a)(j,3),h=d[0],b=d[1],p=d[2];return new Date(Date.UTC(u,o-1,l,h,b,p))},v=["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],g=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];var y=function(t){var e=f(t);return"".concat(v[e.getDay()],", ").concat(g[e.getMonth()]," ").concat(e.getDate(),", ").concat(e.getFullYear())},C=Object(a.createContext)(),w=n(27),k=n(32),N=n(33),P=n(44),E=n(43),T=function(){function t(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};Object(k.a)(this,t),this.id=e.id||null,this.attributes=e.attributes||{},this.dirtyAttributes=[],this.relationships=e.relationships||{}}return Object(N.a)(t,[{key:"type",get:function(){return"generic"}},{key:"hydrate",value:function(t){return this.id=t.id||null,this.attributes=t.attributes||{},this.relationships=t.relationships||{},this}},{key:"setAttribute",value:function(t,e){return this.dirtyAttributes.push(t),this.attributes[t]=e,this}},{key:"getAttribute",value:function(t){return this.attributes[t]}},{key:"toResourceIdentifier",value:function(){return{type:this.type,id:this.id}}},{key:"toResource",value:function(){return{type:this.type,id:this.id,attributes:this.attributes}}},{key:"create",value:function(){var t,e=this,n=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,a={},r=Object(w.a)(this.dirtyAttributes);try{for(r.s();!(t=r.n()).done;){var s=t.value;a[s]=this.attributes[s]}}catch(i){r.e(i)}finally{r.f()}var c={type:this.type,attributes:a};return null!==n&&(c.relationships=n),x("POST","/".concat(this.type),c).then((function(t){return e.dirtyAttributes=[],t.json()})).then((function(t){return t.data}))}},{key:"update",value:function(){var t,e=this,n={},a=Object(w.a)(this.dirtyAttributes);try{for(a.s();!(t=a.n()).done;){var r=t.value;n[r]=this.attributes[r]}}catch(c){a.e(c)}finally{a.f()}var s={type:this.type,id:this.id,attributes:n};return x("PATCH","/".concat(this.type,"/").concat(this.id),s).then((function(){return e.dirtyAttributes=[],e}))}},{key:"delete",value:function(){return x("DELETE","/".concat(this.type,"/").concat(this.id))}},{key:"updateRelationship",value:function(t,e,n){return x(t,"/".concat(this.type,"/").concat(this.id,"/relationships/").concat(e),n)}},{key:"updateToManyRelationship",value:function(t,e,n){return this.updateRelationship(t,e,n.map((function(t){return t.toResourceIdentifier()})))}},{key:"updateToOneRelationship",value:function(t,e,n){return this.updateRelationship(t,e,n.toResourceIdentifier())}}]),t}(),I=function(t){Object(P.a)(n,t);var e=Object(E.a)(n);function n(){return Object(k.a)(this,n),e.apply(this,arguments)}return Object(N.a)(n,[{key:"type",get:function(){return"users"}},{key:"create",value:function(t){var e,n=this,a=this.type,r={},s=Object(w.a)(this.dirtyAttributes);try{for(s.s();!(e=s.n()).done;){var c=e.value;r[c]=this.attributes[c]}}catch(i){s.e(i)}finally{s.f()}return x("POST","/".concat(a),{type:a,attributes:r}).then((function(t){return t.json()})).then((function(e){var a=e.data;return n.hydrate(a),x("PUT","/passwords/".concat(a.id),t)})).then((function(t){return n}))}},{key:"updatePassword",value:function(t,e){return x("PATCH","/passwords/".concat(this.id),{current:t,new:e})}},{key:"login",value:function(t){return x("POST","/session",{username:this.getAttribute("username"),password:t})}},{key:"logout",value:function(){return x("DELETE","/session")}},{key:"subscribeTo",value:function(t){return this.updateToManyRelationship("POST","subscriptions",[t])}},{key:"unsubscribeFrom",value:function(t){return this.updateToManyRelationship("DELETE","subscriptions",[t])}},{key:"addFavorite",value:function(t){return this.updateToManyRelationship("POST","favorites",[t])}},{key:"removeFavorite",value:function(t){return this.updateToManyRelationship("DELETE","favorites",[t])}},{key:"like",value:function(t){return this.updateToManyRelationship("POST","liked-posts",[t])}},{key:"unlike",value:function(t){return this.updateToManyRelationship("DELETE","liked-posts",[t])}}]),n}(T),A=n(10),S=n(9),G=n(4),F=n(22),L=n(58);var R=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],e=["include=author,tags","fields[users]=username","sort=-createdAt"].concat(Object(L.a)(t));return x("GET","/posts?".concat(e.join("&"))).then((function(t){return t.json()})).then((function(t){var e,n=t.data,a=t.included,r=[],s=Object(w.a)(n);try{var c=function(){var t=e.value,n=t.id,s=t.attributes,c=t.relationships,i={author:c.author.data.id,tags:"tags"in c?c.tags.data.map((function(t){return t.id})):[]};r.push({id:n,image:s.image,title:s.title,text:s.text,createdAt:y(s.createdAt),author:a.find((function(t){var e=t.type,n=t.id;return"users"===e&&n===i.author})).attributes.username,tags:a.filter((function(t){var e=t.type,n=t.id;return"tags"===e&&i.tags.includes(n)})).map((function(t){return t.attributes.text}))})};for(s.s();!(e=s.n()).done;)c()}catch(i){s.e(i)}finally{s.f()}return r}))};var U=function(t,e){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:void 0,a=!1,r=null;return function(){for(var s=this,c=arguments.length,i=new Array(c),u=0;u<c;u++)i[u]=arguments[u];var o=function(){e.apply(s,i),a=!1};a&&window.clearTimeout(r),"function"===typeof n&&n.apply(this,i)?o():(r=window.setTimeout(o,t),a=!0)}},D=n(1);var M=function(){var t=Object(a.useContext)(C),e=Object(i.a)(t,2),n=e[0],r=(e[1],Object(a.useState)([])),s=Object(i.a)(r,2),c=s[0],l=s[1],h=Object(a.useState)(!1),b=Object(i.a)(h,2),p=b[0],x=b[1],f=Object(u.g)(),v=U(1e3,(function(t){var e=t.target;if(""===e.value)p&&(x(!1),R().then(l));else{var n="filter[tags.text][in]=".concat(e.value.split(" ").join(","));x(!0),R([n]).then(l)}}),(function(t){var e=t.key,n=t.target;return"Enter"===e||"Backspace"===e&&""===n.value}));return Object(a.useEffect)((function(){var t=[];null!==n&&t.push("page[size]=10"),R(t).then(l)}),[l,n]),Object(D.jsxs)(D.Fragment,{children:[n&&Object(D.jsxs)(G.a,{children:[Object(D.jsx)(G.a.Group,{children:Object(D.jsx)(G.a.Control,{type:"text",placeholder:"Search for posts by tag...",onChange:v})}),Object(D.jsx)(d.a,{className:"my-2",children:Object(D.jsxs)(j.a,{className:"ml-auto",onClick:function(){return f.push("/post")},children:[Object(D.jsx)(O.a,{className:"mr-2",icon:m.e}),"Create Post"]})})]}),Object(D.jsx)(A.a,{children:c.map((function(t,e){var n=t.id,a=t.image,r=t.title,s=t.author,c=t.text,i=t.createdAt,u=t.tags;return Object(D.jsx)(S.a,{xs:12,sm:6,md:4,children:Object(D.jsxs)(F.a,{className:"my-4",children:[Object(D.jsx)(o.b,{to:"/post/".concat(n),children:Object(D.jsx)(F.a.Img,{src:a})}),Object(D.jsxs)(F.a.Body,{children:[Object(D.jsx)(F.a.Title,{children:r}),Object(D.jsxs)(F.a.Text,{children:[Object(D.jsxs)("small",{className:"text-muted",children:["Posted by ",s," on ",i]}),Object(D.jsx)("p",{children:c}),u.length>0&&Object(D.jsxs)("p",{className:"text-muted",children:["Tags: ",u.join(", ")]})]})]})]})},e)}))}),0===c.length&&Object(D.jsx)("p",{children:"There are currently no posts."}),null===n&&Object(D.jsxs)("div",{className:"my-4 text-center",children:[Object(D.jsxs)("p",{children:["You must be logged in to see more posts. ",Object(D.jsx)(j.a,{variant:"primary",onClick:function(){return f.push("/signin")},children:"Sign in"})]}),Object(D.jsxs)("p",{children:["Don't have an account? ",Object(D.jsx)(j.a,{variant:"primary",onClick:function(){return f.push("/signup")},children:"Sign up"})]})]})]})},B=n(20),V=n(45),z=n(29);var J=function(t){var e=t.post,n=t.session,r=t.onSubmitted,s=Object(a.useState)(""),c=Object(i.a)(s,2),u=c[0],o=c[1],l=Object(a.useRef)(null);return Object(D.jsxs)(G.a,{children:[Object(D.jsx)(G.a.Group,{children:Object(D.jsx)(G.a.Control,{as:"textarea",ref:l,onChange:function(t){var e=t.target;return o(e.value)}})}),Object(D.jsx)(G.a.Group,{children:Object(D.jsxs)(j.a,{variant:"primary",onClick:function(){return x("POST","/comments",{type:"comments",attributes:{text:u},relationships:{author:{data:{type:"users",id:n.user.id}},post:{data:{type:"posts",id:e.id}}}}).then((function(t){return t.json()})).then((function(t){return t.data})).then(r).then((function(){return o("")})).then((function(){l.current.value=""})).catch(console.log)},children:[Object(D.jsx)(O.a,{className:"mr-2",icon:m.e}),"Add Comment"]})})]})};var _=function(){var t=Object(u.g)();return Object(D.jsx)(O.a,{style:{cursor:"pointer"},className:"mr-3",icon:m.a,size:"1x",onClick:function(){return t.replace("/dashboard")}})};var Y=function(t){var e=t.text,n=t.createdAt,a=t.author,r=a.avatar?Object(D.jsx)("img",{style:{width:"64px",height:"64px",borderRadius:"50%",border:"1px solid #ccc"},className:"mr-3",src:a.avatar,alt:a.username}):Object(D.jsx)(O.a,{className:"mr-3",size:"3x",icon:m.j});return Object(D.jsxs)(z.a,{children:[r,Object(D.jsxs)(z.a.Body,{children:[Object(D.jsxs)("small",{className:"text-muted",children:["Posted by ",a," on ",y(n)]}),Object(D.jsx)("p",{children:e})]})]})};var H=function(){var t=Object(a.useContext)(C),e=Object(i.a)(t,1)[0],n=Object(u.h)().id,r=Object(a.useState)(null),s=Object(i.a)(r,2),c=s[0],o=s[1],l=Object(a.useState)([]),j=Object(i.a)(l,2),d=j[0],h=j[1],b=function(){return x("GET","/posts/".concat(n,"/comments?include=author&fields[users]=username,avatar&sort=-createdAt")).then((function(t){return t.json()})).then((function(t){var e=t.data,n=t.included;return e.map((function(t){var e=t.id,a=t.attributes,r=t.relationships,s=n.find((function(t){return t.id===r.author.data.id}));return{id:e,text:a.text,createdAt:a.createdAt,author:{username:s.attributes.username,avatar:s.attributes.avatar}}}))})).then(h)};return Object(a.useEffect)((function(){return x("GET","/posts/".concat(n,"?include=author,tags,pets&fields[users]=username")).then((function(t){return t.json()})).then((function(t){for(var e=t.data,n=t.included,a=e.id,r=e.attributes,s=e.relationships,c=r.image,i=r.title,u=r.text,o=r.createdAt,l={author:s.author.data.id},j=0,d=["tags","pets","likes"];j<d.length;j++){var h=d[j];l[h]=h in s?s[h].data.map((function(t){return t.id})):[]}return{id:a,image:c,title:i,text:u,createdAt:y(o),author:n.find((function(t){return"users"===t.type&&t.id===l.author})).attributes.username,tags:n.filter((function(t){return"tags"===t.type&&l.tags.includes(t.id)})).map((function(t){return t.attributes.text})),pets:n.filter((function(t){return"pets"===t.type&&l.pets.includes(t.id)}))}})).then(o).then(b)}),[o,h]),Object(D.jsx)(D.Fragment,{children:c?Object(D.jsxs)(D.Fragment,{children:[Object(D.jsxs)("h1",{children:[Object(D.jsx)(_,{}),c.title]}),Object(D.jsxs)(F.a,{className:"my-4",children:[Object(D.jsx)(F.a.Img,{src:c.image}),Object(D.jsx)(F.a.Body,{children:Object(D.jsxs)(F.a.Text,{children:[Object(D.jsxs)("small",{className:"text-muted",children:["Posted by ",c.author," on ",c.createdAt]}),Object(D.jsx)("p",{children:c.text}),c.pets.length>0&&Object(D.jsxs)("div",{className:"my-3",children:[Object(D.jsxs)("p",{className:"text-muted",children:[c.author,"'s pets in this post:"]}),Object(D.jsx)(V.a,{children:c.pets.map((function(t,e){return Object(D.jsx)(V.a.Item,{children:Object(D.jsxs)(z.a,{children:[null===t.avatar?Object(D.jsx)(O.a,{icon:m.d,size:"2x",className:"d-block mr-3"}):Object(D.jsx)("img",{style:{width:"64px",height:"64px",borderRadius:"50%"},src:t.avatar,className:"mr-3"}),Object(D.jsx)(z.a.Body,{children:t.name})]})},e)}))})]}),c.tags.length>0&&Object(D.jsxs)("p",{className:"text-muted",children:["Tags: ",c.tags.join(", ")]})]})})]}),e&&Object(D.jsx)(J,{session:e,post:c,onSubmitted:b}),Object(D.jsx)("hr",{}),Object(D.jsxs)("h3",{className:"mb-4",children:["Comments (",d.length,")"]}),d.length>0?d.map((function(t,e){return Object(D.jsx)(Y,Object(B.a)({},t),e)})):Object(D.jsx)("p",{children:"No comments available."})]}):Object(D.jsx)("p",{className:"text-center my-4",children:Object(D.jsx)(O.a,{icon:m.h,size:"3x",pulse:!0})})})};var q=function(){return Object(D.jsx)("h1",{children:"Subscriptions"})};var W=function(){return Object(D.jsx)("h1",{children:"Favorites"})};var Z=function(t){var e=new FormData;return e.append("data",t,t.name),fetch("https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php/upload",{method:"POST",body:e})},$=n(39);var K=function(t){for(var e={},n=0,r=Object.keys(t);n<r.length;n++){e[r[n]]={value:"",dirty:!1,error:null}}var s=Object(a.useState)(e),c=Object(i.a)(s,2),u=c[0],o=c[1];return{get:function(t){return u[t].value},isInvalid:function(t){return u[t].dirty&&null!==u[t].error},getError:function(t){return u[t].error},set:function(e){return U(500,(function(n){var a=n.target;return t[e].reduce((function(t,e){return t.then((function(t){return null!==t&&void 0!==t?t:e(a.value)}))}),Promise.resolve(null)).then((function(t){return o((function(n){return Object(B.a)(Object(B.a)({},n),{},Object($.a)({},e,{value:a.value,dirty:!0,error:t}))})),null===t})).catch((function(){return o((function(t){return Object(B.a)(Object(B.a)({},t),{},Object($.a)({},e,{value:a.value,dirty:!0,error:"Invalid value."}))})),!1}))}),(function(t){var e=t.key,n=t.target;return"Enter"===e||"Backspace"===e&&""===n.value}))},getValidity:function(){return Promise.all(Object.keys(u).map((function(e){return t[e].reduce((function(t,n){return t.then((function(t){return null!==t&&void 0!==t?t:n(u[e].value)}))}),Promise.resolve(null)).then((function(t){return o((function(n){return Object(B.a)(Object(B.a)({},n),{},Object($.a)({},e,{value:u[e].value,dirty:!0,error:t}))})),null===t})).catch((function(t){return o((function(t){return Object(B.a)(Object(B.a)({},t),{},Object($.a)({},e,{value:u[e].value,dirty:!0,error:"Invalid value."}))})),!1}))}))).then((function(t){return t.reduce((function(t,e){return t&&e}),!0)}))}}};var Q=function(){var t=Object(a.useContext)(C),e=Object(i.a)(t,2),n=e[0],r=e[1],s=Object(a.useState)(null),c=Object(i.a)(s,2),u=c[0],o=c[1],l=function(t){return function(e){return Promise.resolve(""===e?"".concat(t," cannot be empty."):null)}},d=K({password:[l("Password")],newPassword:[l("New password"),function(t,e){return Promise.resolve(t===e("password")?"Passwords should not match":null)}]});return Object(D.jsxs)(D.Fragment,{children:[Object(D.jsxs)(G.a,{noValidate:!0,children:[Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Current Password"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.Control,{isInvalid:d.isInvalid("password"),type:"password",placeholder:"Current password",onChange:d.set("password")}),d.isInvalid("password")&&Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:d.getError("password")})]})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"New Password"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.Control,{isInvalid:d.isInvalid("newPassword"),type:"password",placeholder:"New password",onChange:d.set("newPassword")}),d.isInvalid("newPassword")&&Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:d.getError("newPassword")})]})]}),Object(D.jsx)(G.a.Group,{as:A.a,children:Object(D.jsx)(S.a,{sm:{span:10,offset:2},children:Object(D.jsx)(j.a,{variant:"primary",onClick:function(){return n.user.updatePassword(d.get("password"),d.get("newPassword")).then((function(t){window.alert(204===t.code?"Password updated!":"An error occured while attempting to update your password.")}))},children:"Update Password"})})})]}),Object(D.jsx)("hr",{}),Object(D.jsxs)(G.a,{noValidate:!0,children:[Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Avatar"}),Object(D.jsx)(S.a,{sm:10,children:Object(D.jsx)(G.a.File,{custom:!0,label:u?u.name:"Upload an image",onChange:function(t){var e=t.target;return o(e.files.item(0))}})})]}),Object(D.jsx)(G.a.Group,{as:A.a,children:Object(D.jsx)(S.a,{sm:{span:10,offset:2},children:Object(D.jsx)(j.a,{variant:"primary",onClick:function(){return Z(u).then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){return n.user.setAttribute("avatar",t)})).then((function(t){return t.update()})).then((function(t){return r((function(e){return Object(B.a)(Object(B.a)({},e),{},{user:t})}))})).then((function(){return window.alert("Avatar updated!")}))},children:"Update Avatar"})})})]})]})};var X=function(){var t,e=Object(a.useContext)(C),n=Object(i.a)(e,1)[0],r=Object(a.useState)(null),s=Object(i.a)(r,2),c=s[0],u=s[1],o=Object(a.useState)([]),l=Object(i.a)(o,2),d=l[0],h=l[1],b=K({petName:[(t="Pet name",function(e){return Promise.resolve(""===e?"".concat(t," cannot be empty."):null)}),function(t){return Promise.resolve(d.includes(t)?'You already have a pet named "'.concat(t,'."'):null)}]}),p=function(){var t=Promise.resolve(null);return null!==c&&t.then((function(){return Z(c)})).then((function(t){return t.json()})).then((function(t){return t.data})),t.then((function(t){return x("POST","/pets",(e=t,{type:"pets",attributes:{name:b.get("petName"),avatar:e},relationships:{owner:{data:{type:"users",id:n.user.id}}}}));var e})).then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){var e=t.id,n=t.attributes;return h((function(t){return[].concat(Object(L.a)(t),[{id:e,name:n.name,avatar:n.avatar}])}))}))};return Object(a.useEffect)((function(){x("GET","/users/".concat(n.user.id,"/pets")).then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){return t.map((function(t){var e=t.id,n=t.attributes;return{id:e,name:n.name,avatar:n.avatar}}))})).then(h)}),[h]),Object(D.jsxs)(D.Fragment,{children:[Object(D.jsxs)(G.a,{noValidate:!0,children:[Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Pet Name"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.Control,{isInvalid:b.isInvalid("petName"),type:"text",placeholder:"Enter you pet's name",onChange:b.set("petName")}),b.isInvalid("petName")&&Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:b.getError("petName")})]})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Pet Avatar"}),Object(D.jsx)(S.a,{sm:10,children:Object(D.jsx)(G.a.File,{custom:!0,label:"Upload an image",onChange:function(t){var e=t.target;return u(e.files.item(0))}})})]}),Object(D.jsx)(G.a.Group,{as:A.a,children:Object(D.jsx)(S.a,{sm:{span:10,offset:2},children:Object(D.jsx)(j.a,{variant:"primary",onClick:function(){return b.getValidity().then((function(t){return t&&p()}))},children:"Add Pet"})})})]}),Object(D.jsx)("hr",{}),Object(D.jsx)("h3",{children:"Pets"}),d.length>0?Object(D.jsx)(V.a,{children:d.map((function(t,e){return Object(D.jsx)(V.a.Item,{children:Object(D.jsxs)(z.a,{children:[null===t.avatar?Object(D.jsx)(O.a,{icon:m.d,size:"2x",className:"d-block mr-3"}):Object(D.jsx)("img",{style:{width:"64px",height:"64px",borderRadius:"50%"},src:t.avatar,className:"mr-3"}),Object(D.jsx)(z.a.Body,{children:t.name})]})},e)}))}):Object(D.jsx)("p",{children:"You have no pets!"})]})};var tt=function(){var t=Object(u.i)(),e=t.url,n=t.path,r=Object(u.g)(),s=Object(a.useState)("account"),c=Object(i.a)(s,2),o=c[0],l=c[1],h=function(t){return function(){l(t),r.replace("".concat(e,"/").concat(t))}};return Object(D.jsxs)(D.Fragment,{children:[Object(D.jsxs)("h1",{children:[Object(D.jsx)(_,{}),"Settings"]}),Object(D.jsx)(d.a,{className:"d-flex my-4",children:["account","pets"].map((function(t,e){return Object(D.jsx)(j.a,{variant:t===o?"primary":"secondary",onClick:h(t),children:"".concat(t.charAt(0).toUpperCase()).concat(t.substr(1))},e)}))}),Object(D.jsxs)(u.d,{children:[Object(D.jsx)(u.b,{path:"".concat(n,"/account"),children:Object(D.jsx)(Q,{})}),Object(D.jsx)(u.b,{path:"".concat(n,"/pets"),children:Object(D.jsx)(X,{})}),Object(D.jsx)(u.b,{path:"".concat(n,"/subscriptions"),children:Object(D.jsx)("p",{children:"Manage Subscriptions"})}),Object(D.jsx)(u.b,{exact:!0,path:"".concat(n),children:Object(D.jsx)(u.a,{to:"".concat(e,"/account")})})]})]})};var et=function(t){return function(e){if(e.status!==t)throw e.status;return e.json()}};var nt=function(){var t=Object(a.useContext)(C),e=Object(i.a)(t,2),n=(e[0],e[1]),r=Object(a.useState)(""),s=Object(i.a)(r,2),c=s[0],o=s[1],l=Object(a.useState)(""),d=Object(i.a)(l,2),h=d[0],b=d[1],p=Object(u.g)(),O=function(t){return U(500,(function(e){var n=e.target;return t(n.value)}),(function(t){var e=t.key,n=t.target;return"Enter"===e||"Backspace"===e&&""===n.value}))};return Object(D.jsxs)(D.Fragment,{children:[Object(D.jsxs)("h1",{className:"mb-4",children:[Object(D.jsx)(_,{}),"Sign In"]}),Object(D.jsxs)(G.a,{children:[Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Username"}),Object(D.jsx)(S.a,{sm:10,children:Object(D.jsx)(G.a.Control,{type:"text",placeholder:"Enter username",onChange:O(o)})})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Password"}),Object(D.jsx)(S.a,{sm:10,children:Object(D.jsx)(G.a.Control,{type:"password",placeholder:"Enter password",onChange:O(b)})})]}),Object(D.jsx)(G.a.Group,{as:A.a,children:Object(D.jsx)(S.a,{sm:{span:10,offset:2},children:Object(D.jsx)(j.a,{variant:"primary",onClick:function(){return x("POST","/session",{username:c,password:h}).then(et(201)).then((function(t){return t.data})).then((function(t){var e=t.uid;return x("GET","/users/".concat(e,"?include=subscriptions"))})).then((function(t){return t.json()})).then((function(t){var e=t.data,n=t.included;return{user:new I(e),subscriptions:n.map((function(t){return t.id}))}})).then(n).then((function(){return p.replace("/dashboard")})).catch((function(t){console.error(t),window.alert("Invalid username/password combination!")}))},children:"Sign In"})})})]})]})};var at=function(){var t=Object(a.useState)(null),e=Object(i.a)(t,2),n=e[0],r=e[1],s=Object(a.useContext)(C),c=Object(i.a)(s,2),o=(c[0],c[1]),l=Object(u.g)(),d=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"Value";return function(e){return Promise.resolve(""===e?"".concat(t," cannot be empty."):null)}},h=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;return function(n){return x("GET","/users?filter[".concat(t,"]=").concat(n)).then((function(t){return t.json()})).then((function(n){return"undefined"===typeof n.data.pop()?null:"Provided ".concat(e||t," is already in use.")}))}},b=K({username:[d("Username"),h("username")],password:[d("Password")],firstName:[d("First name")],lastName:[d("Last name")],email:[d("E-mail address"),h("email","e-mail address")]}),p=new I;return Object(D.jsxs)(D.Fragment,{children:[Object(D.jsxs)("h1",{className:"mb-4",children:[Object(D.jsx)(_,{}),"Sign Up"]}),Object(D.jsxs)(G.a,{noValidate:!0,children:[Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"First Name"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.Control,{isInvalid:b.isInvalid("firstName"),type:"text",placeholder:"Enter first name",onChange:b.set("firstName")}),b.isInvalid("firstName")&&Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:b.getError("firstName")})]})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Last Name"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.Control,{isInvalid:b.isInvalid("lastName"),type:"text",placeholder:"Enter last name",onChange:b.set("lastName")}),b.isInvalid("lastName")&&Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:b.getError("lastName")})]})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"E-mail Address"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.Control,{isInvalid:b.isInvalid("email"),type:"text",placeholder:"Enter e-mail address",onChange:b.set("email")}),b.isInvalid("email")&&Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:b.getError("email")})]})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Username"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.Control,{isInvalid:b.isInvalid("username"),type:"text",placeholder:"Enter username",onChange:b.set("username")}),b.isInvalid("username")&&Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:b.getError("username")})]})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Password"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.Control,{isInvalid:b.isInvalid("password"),type:"password",placeholder:"Enter password",onChange:b.set("password")}),b.isInvalid("password")&&Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:b.getError("password")})]})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Avatar"}),Object(D.jsx)(S.a,{sm:10,children:Object(D.jsx)(G.a.File,{custom:!0,label:"Upload an image",onChange:function(t){var e=t.target;return r(e.files.item(0))}})})]}),Object(D.jsx)(G.a.Group,{as:A.a,children:Object(D.jsx)(S.a,{sm:{span:10,offset:2},children:Object(D.jsx)(j.a,{variant:"primary",onClick:function(){return b.getValidity().then((function(t){return t&&p.setAttribute("firstName",b.get("firstName")).setAttribute("lastName",b.get("lastName")).setAttribute("email",b.get("email")).setAttribute("username",b.get("username")).create(b.get("password")).then((function(){var t=x("POST","/session",{username:b.get("username"),password:b.get("password")});return null!==n&&t.then((function(){return Z(n)})).then((function(t){return t.json()})).then((function(t){var e=t.data;return p.setAttribute("avatar",e)})).then((function(t){return t.update()})),t})).then((function(){return o({user:p,subscriptions:[]})})).then((function(){return l.replace("/dashboard")})).catch(console.log)}))},children:"Sign Up"})})})]})]})},rt=n(68),st=n(38),ct=function(t){Object(P.a)(n,t);var e=Object(E.a)(n);function n(){return Object(k.a)(this,n),e.apply(this,arguments)}return Object(N.a)(n,[{key:"type",get:function(){return"tags"}}]),n}(T),it=function(t){Object(P.a)(n,t);var e=Object(E.a)(n);function n(){return Object(k.a)(this,n),e.apply(this,arguments)}return Object(N.a)(n,[{key:"type",get:function(){return"posts"}},{key:"create",value:function(){var t,e=this,a=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,r=arguments.length>1&&void 0!==arguments[1]?arguments[1]:[],s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:[],c={},i=Object(w.a)(r);try{for(i.s();!(t=i.n()).done;){var u=t.value;c[u]=!1}}catch(o){i.e(o)}finally{i.f()}return x("GET","/tags?filter[text][in]=".concat(r.join(","))).then((function(t){return t.json()})).then((function(t){var e,n=t.data,a=[],s=Object(w.a)(n);try{for(s.s();!(e=s.n()).done;){var i=e.value,u=new ct(i);c[u.getAttribute("text")]=!0,a.push(u)}}catch(o){s.e(o)}finally{s.f()}var l=r.filter((function(t){return!1===c[t]}));return l.length>0?Promise.all(l.map((function(t){return x("POST","/tags",{type:"tags",attributes:{text:t}})}))).then((function(t){return Promise.all(t.map((function(t){return t.json()})))})).then((function(t){return t.map((function(t){var e=t.data;return new ct(e)}))})).then((function(t){return t.concat(a)})):a})).then((function(t){var e={},n=0;return null!==a&&(e.author={data:a.toResourceIdentifier()},n++),s.length>0&&(e.pets={data:s.map((function(t){return{type:"pets",id:t}}))},n++),t.length>0&&(e.tags={data:t.map((function(t){return t.toResourceIdentifier()}))},n++),n>0?e:null})).then((function(t){return Object(rt.a)(Object(st.a)(n.prototype),"create",e).call(e,t)})).then((function(t){return e.hydrate(t)}))}}]),n}(T);var ut=function(){var t,e,n=Object(a.useState)(null),r=Object(i.a)(n,2),s=r[0],c=r[1],o=Object(a.useState)([]),l=Object(i.a)(o,2),d=l[0],h=l[1],b=Object(a.useState)(!0),p=Object(i.a)(b,2),f=p[0],v=p[1],g=Object(a.useContext)(C),y=Object(i.a)(g,1)[0],w=Object(u.g)(),k=K({title:[(t="Post title",e=10,function(n){return Promise.resolve(n.length<e?"".concat(t," must be ").concat(e," or more characters long."):null)}),function(t,e){return function(n){return Promise.resolve(n.length>e?"".concat(t," length cannot exceed ").concat(e," characters."):null)}}("Post title",35)],text:[],tags:[function(t){return Promise.resolve(/^[A-Za-z ]*$/.test(t)?null:"Each tag must be a word separated by a space.")}]});return Object(a.useEffect)((function(){x("GET","/users/".concat(y.user.id,"/pets")).then((function(t){return t.json()})).then((function(t){var e=t.data;v(!1),h(e.map((function(t){var e=t.id,n=t.attributes;return{id:e,image:n.image,name:n.name,isChecked:!1}})))}))}),[]),Object(D.jsxs)(D.Fragment,{children:[Object(D.jsxs)("h1",{className:"mb-4",children:[Object(D.jsx)(_,{}),"Create Post"]}),Object(D.jsxs)(G.a,{children:[Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Title"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.Control,{type:"text",isInvalid:k.isInvalid("title"),placeholder:"Enter post title",onChange:k.set("title")}),k.isInvalid("title")&&Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:k.getError("title")})]})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Image"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.File,{custom:!0,label:s?s.name:"Upload in image",onChange:function(t){var e=t.target;return c(e.files.item(0))}}),Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:"A post image is required."})]})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Caption"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.Control,{as:"textarea",isInvalid:k.isInvalid("text"),placeholder:"Enter post caption",onChange:k.set("text")}),k.isInvalid("text")&&Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:k.getError("text")})]})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Tags"}),Object(D.jsxs)(S.a,{sm:10,children:[Object(D.jsx)(G.a.Control,{type:"text",isInvalid:k.isInvalid("tags"),placeholder:"Enter tags separated by a space",onChange:k.set("tags")}),k.isInvalid("tags")&&Object(D.jsx)(G.a.Control.Feedback,{type:"invalid",children:k.getError("tags")})]})]}),Object(D.jsxs)(G.a.Group,{as:A.a,children:[Object(D.jsx)(G.a.Label,{column:!0,sm:2,children:"Pets in this post"}),Object(D.jsxs)(S.a,{sm:10,children:[f&&Object(D.jsx)(O.a,{className:"my-3",icon:m.h,pulse:!0}),!f&&d.map((function(t,e){return Object(D.jsx)(G.a.Check,{type:"checkbox",label:t.name,defaultChecked:t.isChecked,onChange:(n=e,function(){return h((function(t){return t[n].isChecked=!t[n].isChecked,t}))})},e);var n})),!f&&0===d.length&&Object(D.jsx)(G.a.Control,{plaintext:!0,readOnly:!0,defaultValue:"You have no pets!"})]})]}),Object(D.jsx)(G.a.Group,{as:A.a,children:Object(D.jsx)(S.a,{sm:{span:10,offset:2},children:Object(D.jsxs)(j.a,{variant:"primary",onClick:function(){return k.getValidity().then((function(t){return t&&function(){if(null!==s){var t=k.get("tags").split(" ").map((function(t){return t.trim().toLowerCase()})).filter((function(t){return t.length>0})),e=d.map((function(t){return{type:"pets",id:t.id}}));Z(s).then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){return(new it).setAttribute("image",t).setAttribute("title",k.get("title")).setAttribute("text",k.get("text"))})).then((function(n){return n.create(y.user,t,e)})).then((function(t){return w.replace("/post/".concat(t.id))}))}else window.alert("Posts need an image!")}()}))},children:[Object(D.jsx)(O.a,{icon:m.e,className:"mr-2"}),"Create Post"]})})})]})]})};function ot(){var t=Object(u.g)();return Object(D.jsx)(h.a,{className:"ml-auto",children:Object(D.jsxs)(b.a,{title:Object(D.jsx)(O.a,{icon:m.j,size:"2x"}),children:[Object(D.jsxs)(b.a.Item,{onClick:function(){return t.push("/signin")},children:[Object(D.jsx)(O.a,{className:"mr-2",icon:m.f})," Sign In"]}),Object(D.jsxs)(b.a.Item,{onClick:function(){return t.push("/signup")},children:[Object(D.jsx)(O.a,{className:"mr-2",icon:m.i})," Sign Up"]})]})})}function lt(){var t=Object(a.useContext)(C),e=Object(i.a)(t,2),n=e[0],r=e[1],s=Object(u.g)(),c=null===n.user.getAttribute("avatar")?Object(D.jsx)(O.a,{icon:m.j,size:"2x"}):Object(D.jsx)("img",{style:{borderRadius:"50%",border:"1px solid #888",width:"48px",height:"48px"},src:n.user.getAttribute("avatar")});return Object(D.jsx)(h.a,{className:"ml-auto",children:Object(D.jsxs)(b.a,{className:"text-center",title:c,children:[Object(D.jsx)(b.a.ItemText,{className:"text-center",children:n.user.getAttribute("username")}),Object(D.jsx)(b.a.Divider,{}),Object(D.jsxs)(b.a.Item,{as:o.b,to:"/settings",children:[Object(D.jsx)(O.a,{className:"mr-2",icon:m.c}),"Settings"]}),Object(D.jsxs)(b.a.Item,{onClick:function(){return x("DELETE","/session").then((function(){return r(null)})).then((function(){return s.push("/")}))},children:[Object(D.jsx)(O.a,{className:"mr-2",icon:m.g}),"Sign Out"]})]})})}function jt(){var t=Object(u.i)().url;return Object(D.jsx)(D.Fragment,{children:Object(D.jsxs)(u.d,{children:[Object(D.jsx)(u.b,{path:"".concat(t,"/explore"),children:Object(D.jsx)(M,{})}),Object(D.jsx)(u.b,{path:"".concat(t,"/subscriptions"),children:Object(D.jsx)(q,{})}),Object(D.jsx)(u.b,{path:"".concat(t,"/favorites"),children:Object(D.jsx)(W,{})}),Object(D.jsx)(u.b,{path:"".concat(t,"/"),children:Object(D.jsx)(u.a,{to:"".concat(t,"/explore")})})]})})}var dt=function(t){var e=t.title,n=Object(a.useState)(null),r=Object(i.a)(n,2),s=r[0],c=r[1];return Object(a.useEffect)((function(){x("GET","/session").then((function(t){return t.json()})).then((function(t){return t.data.uid})).then((function(t){return x("GET","/users/".concat(t))})).then((function(t){return t.json()})).then((function(t){return{user:new I(t.data)}})).then(c).catch((function(t){return console.log("Not logged in")}))}),[]),Object(D.jsx)(C.Provider,{value:n,children:Object(D.jsxs)(o.a,{basename:"/~cen4010_s21_g01",children:[Object(D.jsx)(p.a,{className:"mb-4",bg:"dark",variant:"dark",expand:"lg",children:Object(D.jsxs)(l.a,{children:[Object(D.jsxs)(p.a.Brand,{as:o.b,to:"/",children:[e,Object(D.jsx)(O.a,{className:"ml-2",icon:m.b})]}),Object(D.jsx)(p.a.Toggle,{"aria-controls":"main-nav"}),Object(D.jsx)(p.a.Collapse,{id:"main-nav",children:s?Object(D.jsx)(lt,{}):Object(D.jsx)(ot,{})})]})}),Object(D.jsx)(l.a,{children:Object(D.jsxs)(u.d,{children:[Object(D.jsx)(u.b,{path:"/dashboard",children:Object(D.jsx)(jt,{})}),Object(D.jsx)(u.b,{path:"/post/:id",children:Object(D.jsx)(H,{})}),Object(D.jsx)(u.b,{path:"/post",children:Object(D.jsx)(ut,{})}),Object(D.jsx)(u.b,{path:"/signin",children:Object(D.jsx)(nt,{})}),Object(D.jsx)(u.b,{path:"/signup",children:Object(D.jsx)(at,{})}),Object(D.jsx)(u.b,{path:"/settings",children:Object(D.jsx)(tt,{})}),Object(D.jsx)(u.b,{exact:!0,path:"/",children:Object(D.jsx)(u.a,{to:"/dashboard"})})]})})]})})};n(86);c.a.render(Object(D.jsx)(r.a.StrictMode,{children:Object(D.jsx)(dt,{title:"The Pet Park"})}),document.getElementById("root"))}},[[87,1,2]]]);
//# sourceMappingURL=main.99d3e9c2.chunk.js.map