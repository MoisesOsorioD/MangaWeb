export function asText(value: unknown, fallback='Sin título'): string {return typeof value==='string'&&value.trim()?value:fallback;}
export function readPercent(page:number,total:number):number {return total>0?Math.min(100,Math.round((page/total)*100)):0;}
export function prettyDate(value?:string|null):string {
 if(!value)return 'Sin fecha';const d=new Date(value.endsWith('Z')?value:value.replace(' ','T')+'Z');
 return Number.isNaN(d.valueOf())?'Fecha disponible':d.toLocaleDateString('es',{year:'numeric',month:'short',day:'numeric'});
}
