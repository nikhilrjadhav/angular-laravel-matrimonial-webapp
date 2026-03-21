import { Routes } from '@angular/router';
import { environment } from '../environments/environment.prod';
import { LanguageResolver } from './core/resolvers/language.resolver';

export const routes: Routes = [
  // Default route redirects to English version
  { path: '', redirectTo: environment.i18n.defaultLanguage, pathMatch: 'full' },
  {
    //  Public website routes with common layout (Language-Based)
    path: ':lang',
    resolve: { lang: LanguageResolver },
    loadComponent: () => import('./core/layouts/public-layout').then(m => m.PublicLayoutComponent),
    children: [
      { path: '', loadComponent: () => import('./features/home/home.component').then(m => m.HomeComponent) },


    ]
  },

];
