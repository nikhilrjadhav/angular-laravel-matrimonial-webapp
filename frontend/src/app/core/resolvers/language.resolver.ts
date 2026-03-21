import { Injectable } from '@angular/core';
import { LanguageService } from '../services/language.service';
import { ActivatedRouteSnapshot } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class LanguageResolver {

  constructor(private languageService: LanguageService) {}

  resolve(route: ActivatedRouteSnapshot): void {
    this.languageService.initFromRoute(route);
  }
}
