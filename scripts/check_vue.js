// Requiere npm install; prueba que todos los SFC se puedan analizar.
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';
import { parse, compileTemplate, compileScript } from '@vue/compiler-sfc';
const root=new URL('../resources/js/', import.meta.url).pathname;
const files=[];function walk(p){for(const n of readdirSync(p)){const f=join(p,n);if(statSync(f).isDirectory())walk(f);else if(f.endsWith('.vue'))files.push(f);}}walk(root);
let failures=0;for(const file of files){const name=relative(root,file);const source=readFileSync(file,'utf8');const result=parse(source,{filename:file});if(result.errors.length){console.error(name,result.errors);failures++;continue;}
 try{compileScript(result.descriptor,{id:'mangaweb-'+name});}catch(e){console.error(name,'Script:',e.message);failures++;}
 const templ=result.descriptor.template;if(templ){const compiled=compileTemplate({source:templ.content,filename:file,id:'mangaweb-'+name});if(compiled.errors.length){console.error(name,'Template:',compiled.errors);failures++;}}
}
console.log(`Vue SFC analizados: ${files.length}. Errores: ${failures}.`);if(failures)process.exit(1);
